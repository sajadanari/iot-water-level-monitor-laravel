<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LedControllQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LedControllController extends Controller
{
    // Get the earliest not proccesed led control queue record for a specific device
    public function getCommand(Request $request): JsonResponse
    {
        try{

            $validated = $request->validate([
                'device_id' => 'required|string',
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 400);
        }

        $queue = LedControllQueue::where('device_id', $validated['device_id'])
            ->notProcessed()
            ->orderBy('created_at', 'asc')
            ->select('id as queue_id', 'command')
            ->first();

        if (!$queue) {
            return response()->json([
                'status' => 'error',
                'message' => 'No command found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $queue,
            'message' => 'Command retrieved successfully',
        ], 200);

    }

    // Update the led control queue record as processed
    public function processCommand(Request $request): JsonResponse
    {
        try{

            $validated = $request->validate([
                'device_id' => 'required|string|exists:led_controll_queues,device_id',
                'queue_id'  => 'required|integer|exists:led_controll_queues,id',
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 400);
        }

        $queue = LedControllQueue::where('id', $validated['queue_id'])
            ->where('device_id', $validated['device_id'])
            ->first();

        if (!$queue) {
            return response()->json([
                'status' => 'error',
                'message' => 'Command not found.',
            ], 404);
        }

        $queue->update([
            'processed' => true,
            'processed_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Command processed successfully.',
        ], 200);
    }

}
