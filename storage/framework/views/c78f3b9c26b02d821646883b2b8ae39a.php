<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Sistema de Gestão de Demandas'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #2c3e50;
        }

        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 15px 20px;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #34495e;
            color: #fff;
        }

        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-3 text-white">
                    <h4>Sistema de Demandas</h4>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php if(request()->routeIs('dashboard')): ?> active <?php endif; ?>"
                            href="<?php echo e(route('dashboard')); ?>">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if(request()->routeIs('demandas.*')): ?> active <?php endif; ?>"
                            href="<?php echo e(route('demandas.index')); ?>">
                            <i class="bi bi-file-text"></i> Demandas
                        </a>
                    </li>
                    <?php if(auth()->guard()->check()): ?>
                        <?php if(auth()->user()->isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php if(request()->routeIs('users.*')): ?> active <?php endif; ?>"
                                    href="<?php echo e(route('users.index')); ?>">
                                    <i class="bi bi-people-fill"></i> Usuários
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php if(request()->routeIs('clientes.*')): ?> active <?php endif; ?>"
                                    href="<?php echo e(route('clientes.index')); ?>">
                                    <i class="bi bi-people"></i> Clientes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php if(request()->routeIs('status.*')): ?> active <?php endif; ?>"
                                    href="<?php echo e(route('status.index')); ?>">
                                    <i class="bi bi-tag"></i> Status
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <div class="p-3">
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-outline-light w-100">
                            <i class="bi bi-box-arrow-right"></i> Sair
                        </button>
                    </form>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 main-content p-4">
                <?php if(session('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo e(session('success')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if(session('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo e(session('error')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if($errors->any()): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php echo $__env->yieldContent('content'); ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php echo $__env->yieldContent('scripts'); ?>
</body>

</html>
<?php /**PATH /Users/ronefalcao/Documents/IAS/demandas/resources/views/layouts/app.blade.php ENDPATH**/ ?>