# Water Level Monitor API Documentation

## Base URL
```
http://your-domain.com/api/v1
```

## Authentication
Currently, the API is open for IoT devices. Consider implementing API key authentication for production use.

## Rate Limiting
- **IoT Devices**: 300 requests per minute
- **General API**: 60 requests per minute

## Endpoints

### 1. Store Water Level Reading
**POST** `/water-levels/` or `/data/receive` (legacy)

Store a new water level reading from an IoT device.

#### Request Body
```json
{
    "device_id": "device_001",
    "level_cm": 45.5,
    "timestamp": "2024-01-15T10:30:00Z",
    "battery_level": 85.2,
    "temperature": 23.5
}
```

#### Required Fields
- `device_id` (string, max 50 chars): Unique device identifier
- `level_cm` (numeric, 0-1000): Water level in centimeters

#### Optional Fields
- `timestamp` (ISO 8601 date): Device timestamp
- `battery_level` (numeric, 0-100): Battery percentage
- `temperature` (numeric, -40 to 85): Temperature in Celsius

#### Response
```json
{
    "status": "success",
    "message": "Water level data recorded successfully.",
    "data": {
        "id": 1,
        "device_id": "device_001",
        "level_cm": 45.5,
        "level_percentage": 23,
        "status": "moderate",
        "raw_data": {...},
        "created_at": "2024-01-15T10:30:00Z"
    }
}
```

### 2. Get Latest Reading
**GET** `/water-levels/{deviceId}`

Get the latest water level reading for a specific device.

#### Response
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "device_id": "device_001",
        "level_cm": 45.5,
        "level_percentage": 23,
        "status": "moderate",
        "raw_data": {...},
        "created_at": "2024-01-15T10:30:00Z"
    }
}
```

### 3. Get Reading History
**GET** `/water-levels/{deviceId}/history`

Get historical water level readings for a specific device.

#### Query Parameters
- `hours` (integer, default: 24): Number of hours to look back
- `limit` (integer, default: 100, max: 1000): Maximum number of records

#### Example
```
GET /water-levels/device_001/history?hours=48&limit=200
```

#### Response
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "device_id": "device_001",
            "level_cm": 45.5,
            "level_percentage": 23,
            "status": "moderate",
            "created_at": "2024-01-15T10:30:00Z"
        }
    ],
    "meta": {
        "device_id": "device_001",
        "hours": 48,
        "count": 1
    }
}
```

### 4. Get Device Statistics
**GET** `/water-levels/{deviceId}/stats`

Get statistical data for a specific device.

#### Query Parameters
- `hours` (integer, default: 24): Number of hours to analyze

#### Response
```json
{
    "status": "success",
    "data": {
        "device_id": "device_001",
        "period_hours": 24,
        "statistics": {
            "avg_level": 45.5,
            "min_level": 30.2,
            "max_level": 60.8,
            "total_readings": 144,
            "avg_battery": 85.2,
            "avg_temperature": 23.5
        },
        "latest_reading": {
            "id": 1,
            "device_id": "device_001",
            "level_cm": 45.5,
            "level_percentage": 23,
            "status": "moderate",
            "created_at": "2024-01-15T10:30:00Z"
        }
    }
}
```

## Error Responses

### Validation Error (422)
```json
{
    "status": "error",
    "message": "Validation failed.",
    "errors": {
        "device_id": ["Device ID is required."],
        "level_cm": ["Water level must be a number."]
    }
}
```

### Not Found (404)
```json
{
    "status": "error",
    "message": "No water level data found for device."
}
```

### Server Error (500)
```json
{
    "status": "error",
    "message": "Failed to record water level data."
}
```

### Rate Limit Exceeded (429)
```json
{
    "message": "Too Many Attempts."
}
```

## Status Levels
- `excellent`: 80-100%
- `good`: 60-79%
- `moderate`: 40-59%
- `low`: 20-39%
- `critical`: 0-19%

## Example IoT Device Code (Arduino/ESP8266)

```cpp
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>

const char* ssid = "your_wifi";
const char* password = "your_password";
const char* serverUrl = "http://your-domain.com/api/v1/water-levels/";

void setup() {
    Serial.begin(115200);
    WiFi.begin(ssid, password);
    
    while (WiFi.status() != WL_CONNECTED) {
        delay(1000);
        Serial.println("Connecting to WiFi...");
    }
}

void loop() {
    if (WiFi.status() == WL_CONNECTED) {
        HTTPClient http;
        http.begin(serverUrl);
        http.addHeader("Content-Type", "application/json");
        
        // Read sensor data
        float waterLevel = readWaterLevelSensor();
        float batteryLevel = readBatteryLevel();
        float temperature = readTemperatureSensor();
        
        // Create JSON payload
        DynamicJsonDocument doc(1024);
        doc["device_id"] = "device_001";
        doc["level_cm"] = waterLevel;
        doc["battery_level"] = batteryLevel;
        doc["temperature"] = temperature;
        
        String jsonString;
        serializeJson(doc, jsonString);
        
        int httpResponseCode = http.POST(jsonString);
        
        if (httpResponseCode > 0) {
            String response = http.getString();
            Serial.println("Response: " + response);
        } else {
            Serial.println("Error on HTTP request");
        }
        
        http.end();
    }
    
    delay(300000); // Wait 5 minutes
}
```

## Best Practices

1. **Always include device_id** in your requests
2. **Use HTTPS** in production
3. **Implement retry logic** in your IoT devices
4. **Monitor battery levels** and temperature
5. **Use appropriate intervals** for data transmission
6. **Handle network failures** gracefully
7. **Validate data** before sending
8. **Use consistent timestamps** (ISO 8601 format)
