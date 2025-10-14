<?php
// Telegram Bot Webapp - SMM Panel
session_start();

// Konfiqurasiya faylını yüklə
require_once 'config/database.php';
require_once 'config/telegram.php';

// Əsas səhifə
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMM Panel - Telegram Bot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">SMM Panel</h4>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#" onclick="showSection('dashboard')">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#" onclick="showSection('services')">
                                <i class="fas fa-shopping-cart"></i> Xidmətlər
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#" onclick="showSection('orders')">
                                <i class="fas fa-list"></i> Sifarişlər
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#" onclick="showSection('balance')">
                                <i class="fas fa-wallet"></i> Balans
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#" onclick="showSection('profile')">
                                <i class="fas fa-user"></i> Profil
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2" id="page-title">Dashboard</h1>
                </div>

                <!-- Dashboard Section -->
                <div id="dashboard-section" class="content-section">
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Ümumi Sifarişlər</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-orders">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Balans</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="user-balance">0 AZN</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Aktiv Sifarişlər</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="active-orders">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Xidmətlər</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-services">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-list fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Services Section -->
                <div id="services-section" class="content-section" style="display: none;">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Mövcud Xidmətlər</h6>
                        </div>
                        <div class="card-body">
                            <div class="row" id="services-list">
                                <!-- Xidmətlər burada yüklənəcək -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orders Section -->
                <div id="orders-section" class="content-section" style="display: none;">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Sifarişlərim</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="orders-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Xidmət</th>
                                            <th>Link</th>
                                            <th>Miqdar</th>
                                            <th>Qiymət</th>
                                            <th>Status</th>
                                            <th>Tarix</th>
                                        </tr>
                                    </thead>
                                    <tbody id="orders-tbody">
                                        <!-- Sifarişlər burada yüklənəcək -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Balance Section -->
                <div id="balance-section" class="content-section" style="display: none;">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Balans</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4>Cari Balans: <span id="current-balance">0 AZN</span></h4>
                                    <button class="btn btn-primary" onclick="showAddBalanceModal()">Balans Artır</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Section -->
                <div id="profile-section" class="content-section" style="display: none;">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Profil Məlumatları</h6>
                        </div>
                        <div class="card-body">
                            <form id="profile-form">
                                <div class="form-group">
                                    <label for="username">İstifadəçi Adı</label>
                                    <input type="text" class="form-control" id="username" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="telegram_id">Telegram ID</label>
                                    <input type="text" class="form-control" id="telegram_id" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email">
                                </div>
                                <button type="submit" class="btn btn-primary">Yadda Saxla</button>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Balance Modal -->
    <div class="modal fade" id="addBalanceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Balans Artır</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="add-balance-form">
                        <div class="form-group">
                            <label for="amount">Məbləğ (AZN)</label>
                            <input type="number" class="form-control" id="amount" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="payment_method">Ödəniş Üsulu</label>
                            <select class="form-control" id="payment_method" required>
                                <option value="card">Kart</option>
                                <option value="bank">Bank köçürməsi</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Ödəniş Et</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>