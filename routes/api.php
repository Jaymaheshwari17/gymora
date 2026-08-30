<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// Public Auth Routes (Protected with Rate Limiting against Brute-force attacks)
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'registerOwner']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Protected Routes (Require Authentication)
Route::middleware('auth:sanctum')->group(function () {
    
    // Get authenticated user info
    Route::get('/user', function (Request $request) {
        return $request->user()->load('gym');
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Plans Routes
    Route::get('/plans', [\App\Http\Controllers\Api\PlanController::class, 'index']);
    Route::post('/plans', [\App\Http\Controllers\Api\PlanController::class, 'store']);
    Route::put('/plans/{id}', [\App\Http\Controllers\Api\PlanController::class, 'update']);
    Route::delete('/plans/{id}', [\App\Http\Controllers\Api\PlanController::class, 'destroy']);
    
    // Staff & Trainers Routes
    Route::get('/staff-trainers', [\App\Http\Controllers\Api\StaffTrainerController::class, 'index']);
    Route::post('/staff-trainers', [\App\Http\Controllers\Api\StaffTrainerController::class, 'store']);
    Route::get('/staff-trainers/{id}', [\App\Http\Controllers\Api\StaffTrainerController::class, 'show']);
    Route::put('/staff-trainers/{id}', [\App\Http\Controllers\Api\StaffTrainerController::class, 'update']);
    Route::delete('/staff-trainers/{id}', [\App\Http\Controllers\Api\StaffTrainerController::class, 'destroy']);
    
    // Members Routes
    Route::get('/members', [\App\Http\Controllers\Api\MemberController::class, 'index']);
    Route::post('/members', [\App\Http\Controllers\Api\MemberController::class, 'store']);
    Route::put('/members/{id}', [\App\Http\Controllers\Api\MemberController::class, 'update']);
    Route::delete('/members/{id}', [\App\Http\Controllers\Api\MemberController::class, 'destroy']);
    Route::post('/members/{id}/renew', [\App\Http\Controllers\Api\MemberController::class, 'renew']);
    
    // Gym Settings Routes
    Route::get('/settings/gym', [\App\Http\Controllers\Api\SettingsController::class, 'getGymProfile']);
    Route::post('/settings/gym', [\App\Http\Controllers\Api\SettingsController::class, 'updateGymProfile']);
    Route::post('/settings/push-token', [\App\Http\Controllers\Api\SettingsController::class, 'updatePushToken']);
    
    // Attendance Routes
    Route::post('/attendance/mark', [\App\Http\Controllers\Api\AttendanceController::class, 'mark']);
    
    // Batches Routes
    Route::get('/batches', [\App\Http\Controllers\Api\BatchController::class, 'index']);
    Route::post('/batches', [\App\Http\Controllers\Api\BatchController::class, 'store']);
    Route::put('/batches/{id}', [\App\Http\Controllers\Api\BatchController::class, 'update']);
    Route::delete('/batches/{id}', [\App\Http\Controllers\Api\BatchController::class, 'destroy']);
    
    // Plans Routes
    Route::get('/plans', [\App\Http\Controllers\Api\PlanController::class, 'index']);
    Route::post('/plans', [\App\Http\Controllers\Api\PlanController::class, 'store']);
    Route::put('/plans/{id}', [\App\Http\Controllers\Api\PlanController::class, 'update']);
    Route::delete('/plans/{id}', [\App\Http\Controllers\Api\PlanController::class, 'destroy']);
    
    // Staff & Trainers Routes
    Route::get('/staff-trainers', [\App\Http\Controllers\Api\StaffTrainerController::class, 'index']);
    Route::post('/staff-trainers', [\App\Http\Controllers\Api\StaffTrainerController::class, 'store']);
    Route::get('/staff-trainers/{id}', [\App\Http\Controllers\Api\StaffTrainerController::class, 'show']);
    Route::post('/staff-trainers/{id}', [\App\Http\Controllers\Api\StaffTrainerController::class, 'update']); // Using POST with _method=PUT because of FormData file uploads
    Route::delete('/staff-trainers/{id}', [\App\Http\Controllers\Api\StaffTrainerController::class, 'destroy']);
    
    // Attendance Management
    Route::get('/attendance/members', [App\Http\Controllers\Api\AttendanceController::class, 'getMembersAttendance']);
    Route::post('/attendance/mark', [App\Http\Controllers\Api\AttendanceController::class, 'toggleAttendance']);

    // Dashboard Routes
    Route::get('/dashboard/owner-stats', [\App\Http\Controllers\Api\DashboardController::class, 'getOwnerStats']);
    Route::get('/dashboard/member-stats', [\App\Http\Controllers\Api\DashboardController::class, 'getMemberStats']);
    Route::get('/dashboard/notifications', [\App\Http\Controllers\Api\DashboardController::class, 'getNotifications']);

    // Payments Routes
    Route::get('/payments', [\App\Http\Controllers\Api\PaymentController::class, 'index']);
    Route::put('/payments/{id}', [\App\Http\Controllers\Api\PaymentController::class, 'update']);
    Route::delete('/payments/{id}', [\App\Http\Controllers\Api\PaymentController::class, 'destroy']);

    // Expenses Routes
    Route::get('/expenses', [\App\Http\Controllers\Api\ExpenseController::class, 'index']);
    Route::post('/expenses', [\App\Http\Controllers\Api\ExpenseController::class, 'store']);
    Route::put('/expenses/{id}', [\App\Http\Controllers\Api\ExpenseController::class, 'update']);
    Route::delete('/expenses/{id}', [\App\Http\Controllers\Api\ExpenseController::class, 'destroy']);
    
    // Settings Routes
    Route::put('/settings/profile', [\App\Http\Controllers\Api\SettingsController::class, 'updateProfile']);
    Route::put('/settings/change-password', [\App\Http\Controllers\Api\SettingsController::class, 'changePassword']);
    
    // Diet Plans Routes
    Route::get('/diet-plans', [\App\Http\Controllers\Api\DietWorkoutController::class, 'index']);
    Route::post('/diet-plans', [\App\Http\Controllers\Api\DietWorkoutController::class, 'store']);
    Route::put('/diet-plans/{id}', [\App\Http\Controllers\Api\DietWorkoutController::class, 'update']);
    Route::delete('/diet-plans/{id}', [\App\Http\Controllers\Api\DietWorkoutController::class, 'destroy']);
    Route::post('/diet-plans/{id}/assign', [\App\Http\Controllers\Api\DietWorkoutController::class, 'assign']);
    Route::post('/diet-plans/{id}/duplicate', [\App\Http\Controllers\Api\DietWorkoutController::class, 'duplicate']);
    Route::get('/diet-plans/members-list', [\App\Http\Controllers\Api\DietWorkoutController::class, 'getMembers']);

    // Workout Plans Routes
    Route::get('/workout-plans', [\App\Http\Controllers\Api\DietWorkoutController::class, 'indexWorkouts']);
    Route::post('/workout-plans', [\App\Http\Controllers\Api\DietWorkoutController::class, 'storeWorkout']);
    Route::put('/workout-plans/{id}', [\App\Http\Controllers\Api\DietWorkoutController::class, 'updateWorkout']);
    Route::delete('/workout-plans/{id}', [\App\Http\Controllers\Api\DietWorkoutController::class, 'destroyWorkout']);
    Route::post('/workout-plans/{id}/assign', [\App\Http\Controllers\Api\DietWorkoutController::class, 'assignWorkout']);
    Route::post('/workout-plans/{id}/duplicate', [\App\Http\Controllers\Api\DietWorkoutController::class, 'duplicateWorkout']);

    // Reports Routes
    Route::get('/reports/summary', [\App\Http\Controllers\Api\ReportController::class, 'getSummary']);
});
