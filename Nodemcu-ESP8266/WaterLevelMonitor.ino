#include <ESP8266WiFi.h>        // Library for ESP8266 Wi-Fi connection
#include <ESP8266HTTPClient.h>  // Library for sending HTTP requests
#include <ArduinoJson.h>        // Library for creating JSON payload

// **********************************************
// --- [ PROJECT CONFIGURATION ] ---
// **********************************************

// Wi-Fi Credentials
const char* WIFI_SSID = "YOUR_SSID";         // Enter your Wi-Fi network name (SSID) here
const char* WIFI_PASSWORD = "YOUR_WIFI_PASSWORD";     // Enter your Wi-Fi password here

// Server API Configuration
// The IP address or domain of your web server
const char* SERVER_IP_OR_DOMAIN = "WEB_SERVER_ADDRESS"; 
// The full API endpoint path for POSTing data (e.g., /api/v1/water-levels/)
const char* API_PATH = "/api/v1/water-levels/"; 
// Unique identifier for your device
const char* DEVICE_ID = "water_sensor_node_01";
// Time interval between sending data to the server (in milliseconds).
const long SEND_INTERVAL_MS = 1000;

// LED
// Get Command API
const char* GET_COMMAND_API_PATH = "/api/v1/led-commands/get-command";
// Proccess Command API
const char* PROCCESS_COMMAND_API_PATH = "/api/v1/led-commands/proccess-command";
// LED job time interval (in milliseconds).
const long LED_JOB_INTERVAL_MS = 1000;

// Sensor Pin
const int SENSOR_PIN = A0; // Analog input pin for the water level sensor

// LED Pin
#define LED_PIN 5   // GPIO5 = D1

// **********************************************
// --- [ SENSOR CALIBRATION DATA ] ---
// This data defines the relationship between the raw A0 value and the physical depth in cm.
// **********************************************

// X-axis: Analog Raw Reading (A0) - Must be sorted in ascending order for interpolation.
const int RAW_DATA_POINTS[] = {
    8,    // 0.0 cm
    350,  // 0.1 cm
    400,  // 0.3 cm
    430,  // 0.5 cm
    530,  // 1.0 cm
    550,  // 1.5 cm
    570,  // 2.0 cm
    600,  // 2.5 cm
    640,  // 3.0 cm
    644,  // 3.5 cm
    650   // 4.0 cm (Max depth of 4 cm / 40 mm)
};

// Y-axis: Corresponding Water Depth in Centimeters (cm).
const float DEPTH_DATA_POINTS[] = {
    0.0,
    0.1,
    0.3,
    0.5,
    1.0,
    1.5,
    2.0,
    2.5,
    3.0,
    3.5,
    4.0
};

// Number of points in the calibration table.
const int NUM_POINTS = sizeof(RAW_DATA_POINTS) / sizeof(RAW_DATA_POINTS[0]);

// **********************************************
// --- [ NOISE REDUCTION FILTER CONFIGURATION ] ---
// **********************************************

// Window size for the Moving Average Filter. Higher = smoother reading, slower response.
const int FILTER_WINDOW_SIZE = 10;
int readings[FILTER_WINDOW_SIZE]; // Array to store the last N readings
int readIndex = 0; // Index to store the next reading.
long total = 0; // Total running sum of the readings.

// Variable to store the time of the last successful send
unsigned long lastSendTime = 0;

unsigned long lastLedGetCommandTime = 0;

// Custom struct to store API response
struct LedGetCommandResponse {
  bool success;         // Whether the API request was successful
  String command;       // "on" or "off"
  int queue_id;         // ID of the queued command
  String message;       // Optional message or error
};

// Custom struct to store proccess API response
struct LedProccessCommandResponse {
  bool success;
  String message;
};

// **********************************************
// --- [ FUNCTION DEFINITIONS ] ---
// **********************************************

/**
 * Fetch LED command from the server
 * 
 * @return LedGetCommandResponse containing status and command data
 */
LedGetCommandResponse getLedCommand() {
  LedGetCommandResponse result;

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[WiFi] Disconnected. Attempting to reconnect...");
    connectToWiFi();
    if (WiFi.status() != WL_CONNECTED) {
      result.success = false;
      result.message = "WiFi connection failed.";
      return result;
    }
  }

  String fullUrl = "http://" + String(SERVER_IP_OR_DOMAIN) + String(GET_COMMAND_API_PATH);
  Serial.print("[HTTP] Requesting URL: ");
  Serial.println(fullUrl);

  WiFiClient client;
  HTTPClient http;

  if (!http.begin(client, fullUrl)) {
    result.success = false;
    result.message = "Unable to start HTTP connection.";
    return result;
  }

  http.addHeader("Content-Type", "application/json");

  DynamicJsonDocument doc(256);
  doc["device_id"] = DEVICE_ID;

  String jsonString;
  serializeJson(doc, jsonString);

  Serial.print("[HTTP] Sending JSON: ");
  Serial.println(jsonString);

  int httpResponseCode = http.POST(jsonString);

  String response = http.getString();
  Serial.print("[HTTP] Server Response: ");
  Serial.println(response);

  DynamicJsonDocument responseDoc(512);
  DeserializationError error = deserializeJson(responseDoc, response);

  if (httpResponseCode == 200) {
    Serial.println("[HTTP] 200 OK - Command received.");

    if (error) {
      result.success = false;
      result.message = "JSON parse error: " + String(error.c_str());
    } else {
      result.success = true;
      result.command = responseDoc["data"]["command"] | "none";
      result.queue_id = responseDoc["data"]["queue_id"] | -1;
      result.message = "Command received successfully.";

      Serial.print("[Parsed] Command: ");
      Serial.println(result.command);
      Serial.print("[Parsed] Queue ID: ");
      Serial.println(result.queue_id);
    }

    lastLedGetCommandTime = millis();

  } else {
    Serial.print("[HTTP] Error code: ");
    Serial.println(httpResponseCode);

    result.success = false;

    if (!error && responseDoc.containsKey("message")) {
      result.message = responseDoc["message"].as<String>();
    } else {
      result.message = "HTTP error: " + http.errorToString(httpResponseCode);
    }

    Serial.print("[Error] Message: ");
    Serial.println(result.message);
  }

  http.end();
  return result;
}

LedProccessCommandResponse SendProccessCommand(int queue_id) {
  LedProccessCommandResponse result;

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[WiFi] Disconnected. Attempting to reconnect...");
    connectToWiFi();
    if (WiFi.status() != WL_CONNECTED) {
      result.success = false;
      result.message = "WiFi connection failed.";
      return result;
    }
  }

  String fullUrl = "http://" + String(SERVER_IP_OR_DOMAIN) + String(PROCCESS_COMMAND_API_PATH);
  Serial.print("[HTTP] Requesting URL: ");
  Serial.println(fullUrl);

  WiFiClient client;
  HTTPClient http;

  if (!http.begin(client, fullUrl)) {
    result.success = false;
    result.message = "Unable to start HTTP connection.";
    return result;
  }

  http.addHeader("Content-Type", "application/json");

  DynamicJsonDocument doc(256);
  doc["device_id"] = DEVICE_ID;
  doc["queue_id"] = queue_id;

  String jsonString;
  serializeJson(doc, jsonString);

  Serial.print("[HTTP] Sending JSON: ");
  Serial.println(jsonString);

  int httpResponseCode = http.POST(jsonString);

  String response = http.getString();
  Serial.print("[HTTP] Server Response: ");
  Serial.println(response);

  DynamicJsonDocument responseDoc(512);
  DeserializationError error = deserializeJson(responseDoc, response);

  if (httpResponseCode == 200) {
    Serial.println("[HTTP] 200 OK - Command received.");

    if (error) {
      result.success = false;
      result.message = "JSON parse error: " + String(error.c_str());
    } else {
      result.success = true;
      result.message = "Command processed successfully.";
    }

    lastLedGetCommandTime = millis();

  } else {
    Serial.print("[HTTP] Error code: ");
    Serial.println(httpResponseCode);

    result.success = false;

    if (!error && responseDoc.containsKey("message")) {
      result.message = responseDoc["message"].as<String>();
    } else {
      result.message = "HTTP error: " + http.errorToString(httpResponseCode);
    }

    Serial.print("[Error] Message: ");
    Serial.println(result.message);
  }

  http.end();
  return result;
}

void executeLedCommand(const String &command) {
    if (command == "on") {
        digitalWrite(LED_PIN, HIGH);
        Serial.println("[LED] LED turned ON");
    } else if (command == "off") {
        digitalWrite(LED_PIN, LOW);
        Serial.println("[LED] LED turned OFF");
    } else {
        Serial.print("[LED] Unknown command: ");
        Serial.println(command);
    }
}

/**
 * @brief Performs piecewise linear interpolation (the most accurate method for non-linear data).
 * @param rawValue The analog reading from the sensor (0-1023).
 * @return float The calculated water depth in centimeters.
 */
float interpolateDepth(int rawValue) {
  // 1. Handle Extremes (Conditions outside the calibrated range)
  if (rawValue <= RAW_DATA_POINTS[0]) {
    return DEPTH_DATA_POINTS[0]; // Returns 0.0 cm
  }
  if (rawValue >= RAW_DATA_POINTS[NUM_POINTS - 1]) {
    return DEPTH_DATA_POINTS[NUM_POINTS - 1]; // Returns 4.0 cm
  }

  // 2. Find the correct segment in the calibration table
  int segmentIndex = 0;
  for (int i = 0; i < NUM_POINTS - 1; i++) {
    // Check if the rawValue falls between the current point and the next point.
    if (rawValue >= RAW_DATA_POINTS[i] && rawValue < RAW_DATA_POINTS[i + 1]) {
      segmentIndex = i;
      break;
    }
  }

  // 3. Perform Linear Interpolation for the segment
  long rawStart = RAW_DATA_POINTS[segmentIndex];
  long rawEnd = RAW_DATA_POINTS[segmentIndex + 1];
  float depthStart = DEPTH_DATA_POINTS[segmentIndex];
  float depthEnd = DEPTH_DATA_POINTS[segmentIndex + 1];

  // Map formula for float: Output = OutputStart + (OutputEnd - OutputStart) * (Input - InputStart) / (InputEnd - InputStart)
  float mappedValue = depthStart + (depthEnd - depthStart) * ((float)rawValue - rawStart) / ((float)rawEnd - rawStart);
                      
  return mappedValue;
}

/**
 * @brief Reads the sensor, applies the Moving Average filter, and converts the value to cm.
 * @param outRawValue Pointer to store the filtered raw analog reading for logging/sending.
 * @return float The final, filtered water depth in centimeters.
 */
float readAndProcessSensor(int* outRawValue) {
  // Read new value
  int newReading = analogRead(SENSOR_PIN);

  // Apply Moving Average Filter (for noise reduction)
  total = total - readings[readIndex];
  readings[readIndex] = newReading;
  total = total + newReading;
  readIndex = (readIndex + 1) % FILTER_WINDOW_SIZE;

  // Calculate the average (filtered) raw reading.
  int filteredRawValue = total / FILTER_WINDOW_SIZE;
  
  // Store the filtered raw value in the provided pointer
  *outRawValue = filteredRawValue;

  // Convert to Centimeters using Interpolation
  float waterDepth_cm = interpolateDepth(filteredRawValue);

  return waterDepth_cm;
}

/**
 * @brief Connects the ESP8266 to the configured Wi-Fi network.
 */
void connectToWiFi() {
  Serial.print("Connecting to WiFi: ");
  Serial.println(WIFI_SSID);

  // Set the ESP in Station Mode
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  // Wait for connection
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  Serial.println();

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("WiFi connected successfully! ✅");
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("WiFi connection failed. Please check credentials and try again. ❌");
  }
}

/**
 * @brief Sends the sensor data (level_cm and raw_data) to the web server via HTTP POST.
 * @param level_cm The filtered water level in centimeters.
 * @param raw_data The filtered raw analog reading.
 */
void sendDataToServer(float level_cm, int raw_data) {
  // Always check connection before trying to send data
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi is disconnected. Attempting to reconnect...");
    connectToWiFi();
    if (WiFi.status() != WL_CONNECTED) return; // Exit if reconnect fails
  }

  // Construct the full URL (e.g., http://192.168.1.10/api/v1/water-levels/)
  String fullUrl = "http://" + String(SERVER_IP_OR_DOMAIN) + String(API_PATH);
  
  // **FIX for Obsolete API Error:** We must explicitly declare and pass a WiFiClient object.
  WiFiClient client;
  HTTPClient http;
  
  // Begin the connection, passing the client object as required
  http.begin(client, fullUrl); 
  
  // Set the necessary header for JSON data
  http.addHeader("Content-Type", "application/json"); 
  
  // 1. Prepare JSON Payload
  DynamicJsonDocument doc(256);
  
  // Populate the JSON object based on your API requirements:
  doc["device_id"] = DEVICE_ID;
  // Use 'serialized' to ensure two decimal places are kept in the JSON string
  doc["level_cm"] = serialized(String(level_cm, 2)); 
  doc["raw_data"] = raw_data; 
  // Optionally, you can add 'timestamp' or 'battery_level' here if your API supports it.

  // Convert the JSON object into a String
  String jsonString;
  serializeJson(doc, jsonString);
  
  Serial.print("Sending JSON: ");
  Serial.println(jsonString);

  // 2. Send the HTTP POST Request
  int httpResponseCode = http.POST(jsonString);
  
  // 3. Process the Response
  if (httpResponseCode > 0) {
    // HTTP response code is valid (e.g., 200, 201)
    Serial.print("HTTP Response Code: ");
    Serial.println(httpResponseCode);
    
    // Get the response body from the server
    String response = http.getString();
    Serial.print("Server Response: ");
    Serial.println(response);
    
    // Update last send time only on success
    lastSendTime = millis(); 
    
  } else {
    // Error handling
    Serial.print("Error on HTTP request. Code: ");
    Serial.println(httpResponseCode);
    Serial.print("Error Details: ");
    Serial.println(http.errorToString(httpResponseCode));
  }
  
  // Always close the connection
  http.end();
}

// **********************************************
// --- [ ARDUINO SETUP ] ---
// **********************************************

void setup() {
  // Start the serial communication for debugging
  Serial.begin(115200);
  delay(100);

  pinMode(LED_PIN, OUTPUT);

  Serial.println("\n--- NodeMCU Water Level Sensor Project ---");
  
  // Initialize the readings array for the filter (set all to 0)
  for (int i = 0; i < FILTER_WINDOW_SIZE; i++) {
    readings[i] = 0;
  }
  
  // Connect to Wi-Fi
  connectToWiFi();
}

// **********************************************
// --- [ ARDUINO LOOP ] ---
// **********************************************

void loop() {

  // LED ON/OFF Handle
  if (millis() - lastLedGetCommandTime >= LED_JOB_INTERVAL_MS)
  {
    LedGetCommandResponse ledCommand = getLedCommand();

    if (!ledCommand.success) {
        Serial.print("[LED] Failed to get command: ");
        Serial.println(ledCommand.message);
        return;
    }

    // --- Step 2: Execute LED command ---
    Serial.print("[LED] Queue ID: ");
    Serial.println(ledCommand.queue_id);

    executeLedCommand(ledCommand.command);

    // --- Step 3: Notify server that command was processed ---
    LedProccessCommandResponse ledProcess = SendProccessCommand(ledCommand.queue_id);
    
    if (!ledProcess.success) {
        Serial.print("[LED] Failed to process command: ");
        Serial.println(ledProcess.message);
    }

  }

  // Check if the required time interval has passed
  if (millis() - lastSendTime >= SEND_INTERVAL_MS) {
    
    Serial.println("\n--- Starting Sensor Read and Send Cycle ---");
    
    // 1. Read and Process Sensor Data
    int rawValue; // Variable to hold the filtered raw value
    float depth_cm = readAndProcessSensor(&rawValue);

    // 2. Log Sensor Data Locally
    Serial.print("Calculated Water Depth: ");
    Serial.print(depth_cm, 2); 
    Serial.print(" cm | Filtered Raw A0: ");
    Serial.println(rawValue);
    
    // 3. Send Data to Server
    sendDataToServer(depth_cm, rawValue);
    
    Serial.println("--- Cycle Complete ---");
  }

  // Small delay to prevent the watchdog timer from tripping
  delay(100); 
}