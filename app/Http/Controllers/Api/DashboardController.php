<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardResource;
use App\Models\Appointments;
use App\Models\Patients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use \App\Models\User;
use \App\Models\Billing;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function overview()
    {
        $numberOfDoctors = User::where('role', 'doctor')->count();
        $numberOfSecretaries = User::where('role', 'secretary')->count();
        $numberOfPatients = Patients::count();
        $numberOfAppointments = Appointments::count();
        $totalRevenue = Billing::where('payment_status', 'paid')->sum('amount');
        $todayAppointments = Appointments::where('date', today())->count();
        $data = [
            'numberOfDoctors' => $numberOfDoctors,
            'numberOfSecretaries' => $numberOfSecretaries,
            'numberOfPatients' => $numberOfPatients,
            'numberOfAppointments' => $numberOfAppointments,
            'totalRevenue' => $totalRevenue,
            'todayAppointments' => $todayAppointments
        ];
        return new DashboardResource($data);
    }
    public function appointmentStatistics()
    {
        $statistics = Appointments::selectRaw('status,Count(*) as Count')->groupBy('status')->get();
        return new DashboardResource($statistics->pluck('Count', 'status'));
    }
    public function top5Doctors()
    {
        $top5doctors = Appointments::selectRaw('doctor_id,Count(*) as totalAppointments')->groupBy('doctor_id')->orderByDesc('totalAppointments')->take(5)->get();
        $response = $top5doctors->map(function ($appointment) {
            return [
                'doctor_id' => $appointment->doctor_id,
                'appointments_count' => $appointment->totalAppointments,
                'doctor_name' => $appointment->doctor->user->name
            ];
        });
        return new DashboardResource($response);
    }
    public function revenueReport()
    {
        // if (Gate::denies('hide-revenue')) {
        //    abort(403, 'Your are not authorized to make view');
        // }
        $totalRevenue = Billing::sum('amount');
        $totalRevenueByStatus = Billing::selectRaw('payment_status,Sum(amount) as amount')->groupBy('payment_status')->get();
        $recentTransactions = Billing::orderByDesc('updated_at')->where('payment_status', 'paid')->take(5)->get();
        $TransactionsByMonthsPaid = Billing::where('payment_status', 'paid')->selectRaw('Year(created_at) as year,Month(created_at) as month,Sum(amount) as amount')->groupBy('year', 'month')->orderBy('year', 'asc')->orderBy('month', 'asc')->get();
        $TransactionsByMonthsOverdue = Billing::where('payment_status', 'overdue')->selectRaw('Year(created_at) as year,Month(created_at) as month,Sum(amount) as amount')->groupBy('year', 'month')->orderBy('year', 'asc')->orderBy('month', 'asc')->get();
        $TransactionsByMonthsPending = Billing::where('payment_status', 'pending')->selectRaw('Year(created_at) as year,Month(created_at) as month,Sum(amount) as amount')->groupBy('year', 'month')->orderBy('year', 'asc')->orderBy('month', 'asc')->get();
        $data = ['totalRevenue' => $totalRevenue, 'totalRevenueByStatus' => $totalRevenueByStatus->pluck('amount', 'payment_status'), 'recentTransactions' => $recentTransactions, 'TransactionsByMonthsPaid' => $TransactionsByMonthsPaid, 'TransactionsByMonthsOverdue' => $TransactionsByMonthsOverdue, 'TransactionsByMonthsPending' => $TransactionsByMonthsPending];
        return new DashboardResource($data);
    }
    public function doctoroverview()
    {
        $totalPatients = Appointments::where('doctor_id', Auth::user()->id)->distinct()->count('patient_id');
        $todayAppointments = Appointments::where('date', today())->where('doctor_id', Auth::user()->id)->count();
        $totalAppointments = Appointments::where('doctor_id', Auth::user()->id)->count();
        $data = ['totalPatients' => $totalPatients, 'todayAppointments' => $todayAppointments, 'totalAppointments' => $totalAppointments];
        return new DashboardResource($data);
    }
    public function appointmentsStatus()
    {
        $appointments = Appointments::where('doctor_id', Auth::user()->id)->selectRaw('status, Count(*) as count')->groupBy('status')->pluck('count', 'status');
        return new DashboardResource($appointments);
    }
    public function appointmentsOverMonths()
    {
        $appointments = Appointments::where('doctor_id', Auth::user()->id)
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')
            ->pluck('count', 'month');
        return new DashboardResource($appointments);
    }
}
