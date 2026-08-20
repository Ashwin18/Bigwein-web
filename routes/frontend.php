<?php
/**
 * BIGWEIN FRONTEND + OWNER PORTAL ROUTES
 * Already required in routes/web.php:
 *   require __DIR__.'/frontend.php';
 *
 * Kernel.php already has:
 *   'bw.auth'    => FrontendAuthMiddleware::class,
 *   'owner.auth' => OwnerAuthMiddleware::class,
 */

use App\Http\Controllers\Frontend\FrontendAuthController;
use App\Http\Controllers\Frontend\FrontendController;
use App\Http\Controllers\Frontend\OwnerAuthController;
use App\Http\Controllers\Frontend\OwnerDashboardController;
use App\Http\Controllers\Frontend\OwnerPropertyController;
use App\Http\Controllers\Frontend\OwnerSubscriptionController;
use Illuminate\Support\Facades\Route;

/* ══════════════════════════════════════════════════════
   PUBLIC FRONTEND
══════════════════════════════════════════════════════ */
Route::get('/',                [FrontendController::class, 'home']);
Route::get('/properties',      [FrontendController::class, 'properties']);
Route::get('/property/{slug}', [FrontendController::class, 'propertyDetail']);
Route::get('/projects', [\App\Http\Controllers\Frontend\ProjectMarketplaceController::class,'index'])->name('projects.marketplace');
Route::get('/project/{slug}', [\App\Http\Controllers\Frontend\ProjectMarketplaceController::class,'show'])->name('projects.detail');
Route::post('/project-enquiry', [\App\Http\Controllers\Frontend\ProjectMarketplaceController::class,'enquiry'])->name('projects.enquiry');

/* ══════════════════════════════════════════════════════
   USER AUTH PAGES  (public-facing /login /register)
══════════════════════════════════════════════════════ */
Route::get('/user/login',    [FrontendAuthController::class, 'showLogin']);
Route::get('/user/register', [FrontendAuthController::class, 'showRegister']);

/* Buyer Dashboard Pages (protected by session check inside controller) */
Route::get('/user/dashboard',       [FrontendController::class, 'userDashboard'])->name('user.dashboard');
Route::get('/user/saved',           [FrontendController::class, 'userSaved'])->name('user.saved');
Route::get('/user/enquiries',       [FrontendController::class, 'userEnquiries'])->name('user.enquiries');
Route::get('/user/profile',         [FrontendController::class, 'userProfile'])->name('user.profile');
Route::post('/user/profile/update', [FrontendController::class, 'userProfileUpdate'])->name('user.profile.update');
Route::post('/user/remove-saved',   [FrontendController::class, 'removeSaved']);

/* ══════════════════════════════════════════════════════
   OWNER AUTH
══════════════════════════════════════════════════════ */
Route::prefix('owner')->group(function () {
    Route::get('/register', [OwnerAuthController::class, 'showRegister'])->name('owner.register');
    Route::post('/register',[OwnerAuthController::class, 'register']);
    Route::get('/login',    [OwnerAuthController::class, 'showLogin'])->name('owner.login');
    Route::post('/login',   [OwnerAuthController::class, 'login']);
    Route::get('/register', [OwnerAuthController::class, 'showRegister'])->name('owner.register');
    Route::post('/register',[OwnerAuthController::class, 'register']);
    Route::post('/logout',  [OwnerAuthController::class, 'logout'])->name('owner.logout');
});

/* ══════════════════════════════════════════════════════
   OWNER PORTAL — Protected by owner.auth middleware
══════════════════════════════════════════════════════ */
Route::prefix('owner')->middleware('owner.auth')->group(function () {
    // Dashboard
    Route::get('/dashboard',       [OwnerDashboardController::class, 'index'])->name('owner.dashboard');

    // My Properties
    Route::get('/my-properties',   [OwnerDashboardController::class, 'myProperties'])->name('owner.properties');

    // Post / Edit Property
    Route::get('/post-property',            [OwnerPropertyController::class, 'create'])->name('owner.property.create');
    Route::post('/post-property',           [OwnerPropertyController::class, 'store'])->name('owner.property.store');
    Route::get('/property/{id}/edit',       [OwnerPropertyController::class, 'edit'])->name('owner.property.edit');
    Route::post('/property/{id}/update',    [OwnerPropertyController::class, 'update'])->name('owner.property.update');
    Route::delete('/property/{id}',         [OwnerPropertyController::class, 'destroy'])->name('owner.property.destroy');

    // Gallery AJAX
    Route::post('/property/{id}/gallery',   [OwnerPropertyController::class, 'uploadGallery']);
    Route::delete('/gallery/{imageId}',     [OwnerPropertyController::class, 'deleteGallery']);

    // Subscription
    Route::get('/subscription',                         [OwnerSubscriptionController::class, 'index'])->name('owner.subscription');
    Route::post('/subscription/{packageId}/subscribe',  [OwnerSubscriptionController::class, 'subscribe']);

    // Profile Setup (after registration)
    Route::get('/profile-setup', function() {
        if(!session('bw_customer')) return redirect('/owner/login');
        return view('frontend.owner.profile-setup');
    })->name('owner.profile.setup');

    // Profile
    Route::get('/enquiries',        [OwnerDashboardController::class, 'enquiries'])->name('owner.enquiries');
    Route::get('/profile',          [OwnerDashboardController::class, 'profile'])->name('owner.profile');
    Route::post('/profile/update',  [OwnerDashboardController::class, 'updateProfile']);
    Route::post('/profile/password',[OwnerDashboardController::class, 'changePassword']);

    // Enquiries
    Route::get('/enquiries',        [OwnerDashboardController::class, 'enquiries'])->name('owner.enquiries');
});

/* ══════════════════════════════════════════════════════
   BW JSON API
══════════════════════════════════════════════════════ */
Route::prefix('bw-api')->group(function () {
    // Public
    Route::post('/login',        [FrontendAuthController::class, 'login']);
    Route::post('/register',     [FrontendAuthController::class, 'register']);
    Route::post('/send-otp',     [FrontendAuthController::class, 'sendOtp']);
    Route::post('/verify-otp',   [FrontendAuthController::class, 'verifyOtp']);
    Route::post('/logout',       [FrontendAuthController::class, 'logout']);
    Route::get('/properties',    [FrontendController::class, 'apiProperties']);
    Route::get('/property/{id}', [FrontendController::class, 'apiPropertyDetail']);
    Route::get('/projects',      [FrontendController::class, 'apiProjects']);
    Route::get('/categories',    [FrontendController::class, 'apiCategories']);
    Route::get('/faqs',          [FrontendController::class, 'apiFaqs']);
    Route::get('/sliders',       [FrontendController::class, 'apiSliders']);

    // User-authenticated
    Route::middleware('bw.auth')->group(function () {
        Route::post('/favourite',     [FrontendController::class, 'toggleFavourite']);
        Route::post('/inquire',       [FrontendController::class, 'inquire']);
        Route::get('/my-profile',     [FrontendController::class, 'myProfile']);
        Route::get('/my-favourites',  [FrontendController::class, 'myFavourites']);

    });

    // Owner-authenticated
    Route::middleware('owner.auth')->group(function () {
        Route::get('/owner/stats',      [OwnerDashboardController::class, 'statsApi']);
    });
});






/* ══════════════════════════════════════════════════════
   OWNER / SELLER / BUILDER COMMON KYC
══════════════════════════════════════════════════════ */
Route::prefix('owner')->middleware('owner.auth')->group(function () {
    Route::get('/kyc', [\App\Http\Controllers\OwnerKycController::class, 'index'])->name('owner.kyc');
    Route::post('/kyc', [\App\Http\Controllers\OwnerKycController::class, 'submit'])->name('owner.kyc.submit');
    Route::post('/kyc/skip', [\App\Http\Controllers\OwnerKycController::class, 'skip'])->name('owner.kyc.skip');
});

/* ══════════════════════════════════════════════════════
   ADMIN OWNER KYC VERIFICATION
══════════════════════════════════════════════════════ */
Route::middleware(['auth','checkLogin'])->group(function () {
    Route::get('/owner-kyc-admin', [\App\Http\Controllers\OwnerKycController::class, 'adminIndex'])->name('admin.owner.kyc');
    Route::post('/owner-kyc-admin/{id}/status', [\App\Http\Controllers\OwnerKycController::class, 'adminUpdate'])->name('admin.owner.kyc.status');
});

/* ══════════════════════════════════════════════════════
   BUILDER / DEVELOPER PORTAL
══════════════════════════════════════════════════════ */
Route::prefix('owner')->middleware('owner.auth')->group(function () {
    Route::get('/builder-verification', [\App\Http\Controllers\Frontend\OwnerBuilderController::class,'verification'])->name('owner.builder.verification');
    Route::post('/builder-verification', [\App\Http\Controllers\Frontend\OwnerBuilderController::class,'submitVerification'])->name('owner.builder.verification.submit');

    Route::get('/my-projects', [\App\Http\Controllers\Frontend\OwnerBuilderController::class,'myProjects'])->name('owner.builder.projects');
    Route::get('/project/{id}/edit', [\App\Http\Controllers\Frontend\OwnerBuilderController::class,'editProject'])->name('owner.project.edit');
    Route::post('/project/{id}/update', [\App\Http\Controllers\Frontend\OwnerBuilderController::class,'updateProject'])->name('owner.project.update');
    Route::get('/project-enquiries', [\App\Http\Controllers\Frontend\OwnerBuilderController::class,'enquiries'])->name('owner.project.enquiries');
    Route::post('/project-enquiries/{id}/status', [\App\Http\Controllers\Frontend\OwnerBuilderController::class,'updateEnquiryStatus'])->name('owner.project.enquiries.status');
    Route::get('/post-project', [\App\Http\Controllers\Frontend\OwnerBuilderController::class,'createProject'])->name('owner.project.create');
    Route::post('/post-project', [\App\Http\Controllers\Frontend\OwnerBuilderController::class,'storeProject'])->name('owner.project.store');
});

Route::middleware(['auth','checkLogin'])->group(function () {
    Route::get('/builder-verification-admin', [\App\Http\Controllers\BuilderVerificationController::class,'index'])->name('admin.builder.verification');
    Route::post('/builder-verification-admin/{id}/status', [\App\Http\Controllers\BuilderVerificationController::class,'update'])->name('admin.builder.verification.status');
});

/* ── Routes added by panel fixer ── */
// Owner profile GET route (was missing)
Route::get('/owner/profile', [\App\Http\Controllers\Frontend\OwnerDashboardController::class, 'profile'])->name('owner.profile.page');

/* BIGWEIN BUSINESS MARKETPLACE V3 */
Route::get('/businesses', [\App\Http\Controllers\Frontend\BusinessController::class,'index'])->name('business.index');
Route::get('/business/{slug}', [\App\Http\Controllers\Frontend\BusinessController::class,'show'])->name('business.show');
Route::post('/business/enquiry', [\App\Http\Controllers\Frontend\BusinessController::class,'enquiry'])->name('business.enquiry');
Route::prefix('owner')->middleware('owner.auth')->group(function () {
 Route::get('/my-businesses', [\App\Http\Controllers\Frontend\OwnerBusinessController::class,'index'])->name('owner.business.index');
 Route::get('/list-business', [\App\Http\Controllers\Frontend\OwnerBusinessController::class,'create'])->name('owner.business.create');
 Route::post('/list-business', [\App\Http\Controllers\Frontend\OwnerBusinessController::class,'store'])->name('owner.business.store');
 Route::get('/business-enquiries', [\App\Http\Controllers\Frontend\OwnerBusinessController::class,'enquiries'])->name('owner.business.enquiries');
});
Route::middleware(['auth','checkLogin'])->group(function () {
 Route::get('/business-approvals', [\App\Http\Controllers\BusinessAdminController::class,'index'])->name('admin.business.index');
 Route::get('/business-approvals/{id}', [\App\Http\Controllers\BusinessAdminController::class,'show'])->name('admin.business.show');
 Route::post('/business-approvals/{id}/status', [\App\Http\Controllers\BusinessAdminController::class,'updateStatus'])->name('admin.business.status');
 Route::get('/business-categories', [\App\Http\Controllers\BusinessAdminController::class,'categories'])->name('admin.business.categories');
 Route::get('/business-enquiries-admin', [\App\Http\Controllers\BusinessAdminController::class,'enquiries'])->name('admin.business.enquiries');
 Route::post('/business-enquiries-admin/{id}/status', [\App\Http\Controllers\BusinessAdminController::class,'updateEnquiryStatus'])->name('admin.business.enquiries.status');
 Route::post('/business-categories/save', [\App\Http\Controllers\BusinessAdminController::class,'saveCategory'])->name('admin.business.categories.save');
});


Route::middleware(['auth','checkLogin'])->group(function () {
    Route::get('/builder-project-approvals', [\App\Http\Controllers\BuilderProjectApprovalController::class,'index'])->name('admin.builder.projects.approvals');
    Route::get('/builder-project-approvals/{id}', [\App\Http\Controllers\BuilderProjectApprovalController::class,'show'])->name('admin.builder.projects.show');
    Route::post('/builder-project-approvals/{id}/status', [\App\Http\Controllers\BuilderProjectApprovalController::class,'updateStatus'])->name('admin.builder.projects.status');
});
