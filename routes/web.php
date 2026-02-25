<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReceptionistController;
use App\Http\Controllers\NurseController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\VitalSignController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\InvestigationController;
use App\Http\Controllers\BeneficiaryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\ReportController;

// Public Routes
Route::get('/', function () {
    return \Illuminate\Support\Facades\Redirect::route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    
    // Dashboard Routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Role-based Dashboard Routes
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/receptionist', [ReceptionistController::class, 'dashboard'])->name('receptionist');
        Route::get('/nurse', [NurseController::class, 'dashboard'])->name('nurse');
        Route::get('/doctor', [DoctorController::class, 'dashboard'])->name('doctor');
        Route::get('/pharmacy', [PharmacyController::class, 'dashboard'])->name('pharmacy');
        Route::get('/laboratory', [LaboratoryController::class, 'dashboard'])->name('laboratory');
        Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin');
    });
    
    // Receptionist Routes
    Route::prefix('receptionist')->name('receptionist.')->middleware(['auth', 'role:Receptionist|Admin'])->group(function () {
        Route::get('/', [ReceptionistController::class, 'index'])->name('index');
        Route::get('/dashboard', [ReceptionistController::class, 'dashboard'])->name('dashboard');
        
        // Beneficiary Search & Check-in
        Route::get('/beneficiaries/search', [ReceptionistController::class, 'searchBeneficiary'])->name('beneficiaries.search');
        Route::get('/beneficiaries/{beneficiary}', [ReceptionistController::class, 'showBeneficiary'])->name('beneficiaries.show');
        Route::post('/beneficiaries/{beneficiary}/checkin', [ReceptionistController::class, 'checkIn'])->name('beneficiaries.checkin');
        
        // Encounters
        Route::get('/encounters', [ReceptionistController::class, 'encounters'])->name('encounters.index');
        Route::get('/encounters/{encounter}', [ReceptionistController::class, 'showEncounter'])->name('encounters.show');
        Route::post('/encounters/{encounter}/forward-nurse', [ReceptionistController::class, 'forwardToNurse'])->name('encounters.forward-nurse');
        Route::post('/encounters/{encounter}/cancel', [ReceptionistController::class, 'cancelEncounter'])->name('encounters.cancel');
        
        // History
        Route::get('/history', [ReceptionistController::class, 'history'])->name('history');
        
        // Referrals
        Route::get('/referrals', [ReceptionistController::class, 'referrals'])->name('referrals');
        
        // Reports
        Route::get('/reports', [ReceptionistController::class, 'reports'])->name('reports');
    });
    
    // Patient Routes
    Route::prefix('patients')->name('patients.')->middleware(['auth', 'role:Receptionist|Admin'])->group(function () {
        Route::get('/search', [PatientController::class, 'search'])->name('search');
        Route::get('/register', [PatientController::class, 'create'])->name('register');
        Route::post('/register', [PatientController::class, 'store'])->name('store');
        Route::get('/{patient}', [PatientController::class, 'show'])->name('show');
        Route::get('/{patient}/edit', [PatientController::class, 'edit'])->name('edit');
        Route::put('/{patient}', [PatientController::class, 'update'])->name('update');
    });
    
    // Nurse Routes
    Route::prefix('nurse')->name('nurse.')->middleware(['auth', 'role:Nurse|Admin'])->group(function () {
        Route::get('/', [NurseController::class, 'index'])->name('index');
        Route::get('/dashboard', [NurseController::class, 'dashboard'])->name('dashboard');
        
        // Triage (includes vital signs recording)
        Route::get('/triage', [NurseController::class, 'triageIndex'])->name('triage.index');
        Route::get('/triage/history', [NurseController::class, 'triageHistory'])->name('triage.history');
        Route::get('/triage/report', [NurseController::class, 'triageReport'])->name('triage.report');
        Route::get('/triage/{encounter}/create', [NurseController::class, 'triageCreate'])->name('triage.create');
        Route::post('/triage/{encounter}', [NurseController::class, 'triageStore'])->name('triage.store');
        Route::get('/triage/{encounter}/view', [NurseController::class, 'triageShow'])->name('triage.show');
        Route::get('/triage/{encounter}/edit', [NurseController::class, 'triageEdit'])->name('triage.edit');
        Route::put('/triage/{encounter}', [NurseController::class, 'triageUpdate'])->name('triage.update');
        
        // Drug Administration
        Route::get('/drug-administration', [NurseController::class, 'drugAdministrationIndex'])->name('drug-administration.index');
        Route::get('/drug-administration/{prescriptionItem}', [NurseController::class, 'administerDrug'])->name('drug-administration.show');
        Route::post('/drug-administration/{prescriptionItem}', [NurseController::class, 'storeAdministration'])->name('drug-administration.store');
    });
    
    // Doctor Routes
    Route::prefix('doctor')->name('doctor.')->middleware(['auth', 'role:Doctor|Admin'])->group(function () {
        Route::get('/', [DoctorController::class, 'index'])->name('index');
        Route::get('/dashboard', [DoctorController::class, 'dashboard'])->name('dashboard');
        Route::get('/queue', [DoctorController::class, 'queue'])->name('queue');
        Route::get('/reports', [DoctorController::class, 'reports'])->name('reports');
        
        // Consultation workflow
        Route::get('/consultation/{encounter}/start', [DoctorController::class, 'startConsultation'])->name('consultation.start');
        Route::get('/consultation/drugs/search', [DoctorController::class, 'drugSearch'])->name('consultation.drugs.search');
                Route::post('/consultation/{encounter}/update-clinical-assessment', [DoctorController::class, 'updateClinicalAssessment'])->name('consultation.update-clinical-assessment');
        Route::post('/consultation/{encounter}/update-confirmed-diagnosis', [DoctorController::class, 'updateConfirmedDiagnosis'])->name('consultation.update-confirmed-diagnosis');
        Route::post('/consultation/{encounter}/send-to-lab', [DoctorController::class, 'sendToLab'])->name('consultation.send-to-lab');
        Route::post('/consultation/{encounter}/send-to-pharmacy', [DoctorController::class, 'sendToPharmacy'])->name('consultation.send-to-pharmacy');
        Route::post('/consultation/{encounter}/update-procedures', [DoctorController::class, 'updateProcedures'])->name('consultation.update-procedures');
        Route::post('/consultation/{encounter}/refer-patient', [DoctorController::class, 'referPatient'])->name('consultation.refer-patient');
        Route::post('/consultation/{encounter}/discharge', [DoctorController::class, 'dischargePatient'])->name('consultation.discharge');
        Route::get('/consultation/ward/{ward}/rooms', [DoctorController::class, 'getRoomsByWard'])->name('consultation.ward-rooms');
        Route::get('/consultation/room/{room}/beds', [DoctorController::class, 'getBedsByRoom'])->name('consultation.room-beds');
        Route::post('/consultation/item/{item}/recall', [DoctorController::class, 'recallPrescriptionItem'])->name('consultation.recall-item');
        Route::post('/consultation/{encounter}/service-referral', [DoctorController::class, 'confirmServiceReferral'])->name('consultation.service-referral');
        Route::get('/consultation/{encounter}/service-order-status', [DoctorController::class, 'serviceOrderStatus'])->name('consultation.service-order-status');
        Route::delete('/consultation/{encounter}/service-order/{order}/recall', [DoctorController::class, 'recallServiceOrder'])->name('consultation.service-order-recall');
        Route::delete('/consultation/{encounter}/service-order-item', [DoctorController::class, 'removeServiceOrderItem'])->name('consultation.service-order-item-remove');
        Route::get('/lab-results/{order}', [DoctorController::class, 'viewLabResults'])->name('lab-results.view');
        Route::post('/consultation/{encounter}', [DoctorController::class, 'storeConsultation'])->name('consultation.store');
        Route::get('/consultation/{consultation}', [DoctorController::class, 'showConsultation'])->name('consultation.show');
        Route::post('/consultation/{consultation}/prescription', [DoctorController::class, 'addPrescription'])->name('consultation.prescription');
        Route::post('/consultation/{consultation}/investigation', [DoctorController::class, 'addInvestigation'])->name('consultation.investigation');
        Route::post('/consultation/{consultation}/complete', [DoctorController::class, 'completeConsultation'])->name('consultation.complete');
        Route::get('/consultation-history', [DoctorController::class, 'consultationHistory'])->name('consultation.history');
        
        // Patient search & dashboard
        Route::get('/patients', [DoctorController::class, 'patientSearch'])->name('patients');
        Route::get('/patient/{patient}', [DoctorController::class, 'patientDashboard'])->name('patient.dashboard');
        Route::get('/patient/{patient}/history', [DoctorController::class, 'patientHistory'])->name('patient.history');
    });
    
    // Consultation Routes
    Route::prefix('consultations')->name('consultations.')->middleware(['auth', 'role:Doctor|Admin'])->group(function () {
        Route::get('/', [ConsultationController::class, 'index'])->name('index');
        Route::get('/create/{encounter}', [ConsultationController::class, 'create'])->name('create');
        Route::post('/', [ConsultationController::class, 'store'])->name('store');
        Route::get('/{consultation}', [ConsultationController::class, 'show'])->name('show');
        Route::get('/{consultation}/edit', [ConsultationController::class, 'edit'])->name('edit');
        Route::put('/{consultation}', [ConsultationController::class, 'update'])->name('update');
    });
    
    // Prescription Routes
    Route::prefix('prescriptions')->name('prescriptions.')->middleware(['auth', 'role:Doctor|Pharmacist|Admin'])->group(function () {
        Route::get('/', [PrescriptionController::class, 'index'])->name('index');
        Route::get('/{prescription}', [PrescriptionController::class, 'show'])->name('show');
        Route::post('/{prescription}/dispense', [PrescriptionController::class, 'dispense'])->name('dispense');
    });
    
    // Investigation Routes
    Route::prefix('investigations')->name('investigations.')->middleware(['auth', 'role:Doctor|Lab Technician|Admin'])->group(function () {
        Route::get('/', [InvestigationController::class, 'index'])->name('index');
        Route::get('/create/{encounter}', [InvestigationController::class, 'create'])->name('create');
        Route::post('/', [InvestigationController::class, 'store'])->name('store');
        Route::get('/{investigation}', [InvestigationController::class, 'show'])->name('show');
        Route::post('/{investigation}/results', [InvestigationController::class, 'storeResults'])->name('store-results');
    });
    
    // Pharmacy Routes
    Route::prefix('pharmacy')->name('pharmacy.')->middleware(['auth', 'role:Pharmacist|Admin'])->group(function () {
        Route::get('/', [PharmacyController::class, 'index'])->name('index');
        Route::get('/dashboard', [PharmacyController::class, 'dashboard'])->name('dashboard');
        Route::get('/queue', [PharmacyController::class, 'queue'])->name('queue');
        Route::get('/prescription/{prescription}', [PharmacyController::class, 'showPrescription'])->name('prescription.show');
        Route::post('/item/{item}/dispense', [PharmacyController::class, 'dispenseItem'])->name('item.dispense');
        Route::post('/prescription/{prescription}/bulk-dispense', [PharmacyController::class, 'dispenseBulk'])->name('prescription.bulk-dispense');
        Route::get('/history', [PharmacyController::class, 'history'])->name('history');
    });
    
    // Dispensation Routes (legacy — redirect to pharmacy.queue)
    Route::prefix('dispensation')->name('dispensation.')->middleware(['auth', 'role:Pharmacist|Admin'])->group(function () {
        Route::get('/', [PharmacyController::class, 'dispensationIndex'])->name('index');
        Route::get('/{prescription}', [PharmacyController::class, 'showPrescription'])->name('show');
        Route::get('/{prescriptionItem}/dispense', function(string $prescriptionItem): \Illuminate\Http\RedirectResponse { return redirect()->route('pharmacy.queue'); })->name('dispense');
        Route::post('/{prescriptionItem}/dispense', function(string $prescriptionItem): \Illuminate\Http\RedirectResponse { return redirect()->route('pharmacy.queue'); })->name('store-dispensation');
    });
    
    // Stock Management Routes
    Route::prefix('stock-management')->name('stock-management.')->middleware(['auth', 'role:Pharmacist|Admin'])->group(function () {
        Route::get('/', [PharmacyController::class, 'stockIndex'])->name('index');
        Route::get('/{drug}/stock', [PharmacyController::class, 'drugStock'])->name('drug-stock');
        Route::post('/{drug}/stock', [PharmacyController::class, 'updateStock'])->name('update-stock');
    });
    
    // Laboratory Routes
    Route::prefix('laboratory')->name('laboratory.')->middleware(['auth', 'role:Lab Technician|Admin'])->group(function () {
        Route::get('/dashboard', [LaboratoryController::class, 'dashboard'])->name('dashboard');
        Route::get('/queue', [LaboratoryController::class, 'queue'])->name('queue');
        Route::get('/history', [LaboratoryController::class, 'history'])->name('history');
        Route::get('/order/{item}', [LaboratoryController::class, 'orderShow'])->name('order.show');
        Route::post('/order/{item}/status', [LaboratoryController::class, 'updateStatus'])->name('order.status');
        Route::post('/order/{item}/result', [LaboratoryController::class, 'recordResult'])->name('order.result');
    });
    
    // Lab Orders Routes (legacy – redirect to new routes)
    Route::prefix('lab-orders')->name('lab-orders.')->middleware(['auth', 'role:Lab Technician|Doctor|Admin'])->group(function () {
        Route::get('/', [LaboratoryController::class, 'ordersIndex'])->name('index');
        Route::get('/{any}', fn() => \Illuminate\Support\Facades\Redirect::route('laboratory.queue'))->name('show');
    });
    
    // Lab Results Routes (legacy)
    Route::prefix('lab-results')->name('lab-results.')->middleware(['auth', 'role:Lab Technician|Admin'])->group(function () {
        Route::get('/upload', [LaboratoryController::class, 'uploadResults'])->name('upload');
        Route::post('/upload', [LaboratoryController::class, 'storeUpload'])->name('store-upload');
        Route::get('/{order}/create', [LaboratoryController::class, 'createResult'])->name('create');
        Route::post('/{order}/store', [LaboratoryController::class, 'storeResult'])->name('store');
        Route::get('/{investigation}/results', [LaboratoryController::class, 'viewResults'])->name('view');
    });
    
    // Admin Routes
    Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:Admin'])->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // Beneficiary Management
        Route::prefix('beneficiaries')->name('beneficiaries.')->group(function () {
            Route::get('/', [BeneficiaryController::class, 'index'])->name('index');
            Route::get('/create', [BeneficiaryController::class, 'create'])->name('create');
            Route::post('/', [BeneficiaryController::class, 'store'])->name('store');
            Route::get('/{beneficiary}', [BeneficiaryController::class, 'show'])->name('show');
            Route::get('/{beneficiary}/edit', [BeneficiaryController::class, 'edit'])->name('edit');
            Route::put('/{beneficiary}', [BeneficiaryController::class, 'update'])->name('update');
            Route::delete('/{beneficiary}', [BeneficiaryController::class, 'destroy'])->name('destroy');
        });
        
        // User Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}', [UserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        });
        
        // Facility Management
        Route::prefix('facilities')->name('facilities.')->group(function () {
            Route::get('/', [FacilityController::class, 'index'])->name('index');
            Route::get('/create', [FacilityController::class, 'create'])->name('create');
            Route::post('/', [FacilityController::class, 'store'])->name('store');
            Route::get('/{facility}', [FacilityController::class, 'show'])->name('show');
            Route::get('/{facility}/edit', [FacilityController::class, 'edit'])->name('edit');
            Route::put('/{facility}', [FacilityController::class, 'update'])->name('update');
            Route::delete('/{facility}', [FacilityController::class, 'destroy'])->name('destroy');
        });
        
        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/encounters', [ReportController::class, 'encounters'])->name('encounters');
            Route::get('/prescriptions', [ReportController::class, 'prescriptions'])->name('prescriptions');
            Route::get('/investigations', [ReportController::class, 'investigations'])->name('investigations');
            Route::get('/stock', [ReportController::class, 'stock'])->name('stock');
        });
    });
});
