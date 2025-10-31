<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
    </div>

    <!-- Totais por Status -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Total de Demandas por Status</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php $__currentLoopData = $totais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statusId => $dados): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-3 mb-3">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle p-3"
                                                style="background-color: <?php echo e($dados['cor']); ?>; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                                <span class="text-white fw-bold"><?php echo e($dados['total']); ?></span>
                                            </div>
                                            <div class="ms-3">
                                                <h6 class="mb-0"><?php echo e($dados['nome']); ?></h6>
                                                <p class="text-muted mb-0"><?php echo e($dados['total']); ?>

                                                    <?php echo e(Str::plural('demanda', $dados['total'])); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Totais Gerais -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <h5 class="text-muted"><i class="bi bi-people-fill"></i> Total de Usuários</h5>
                    <h1 class="display-3 fw-bold text-primary"><?php echo e($totalUsuarios); ?></h1>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <h5 class="text-muted"><i class="bi bi-folder"></i> Total de Projetos</h5>
                    <h1 class="display-3 fw-bold text-danger"><?php echo e($totalProjetos); ?></h1>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <h5 class="text-muted"><i class="bi bi-people"></i> Total de Clientes</h5>
                    <h1 class="display-3 fw-bold text-success"><?php echo e($totalClientes); ?></h1>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <h5 class="text-muted"><i class="bi bi-file-text"></i> Total de Demandas</h5>
                    <h1 class="display-3 fw-bold text-warning"><?php echo e($totalDemandas); ?></h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Demandas Recentes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Demandas Recentes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Data</th>
                                    <th>Cliente</th>
                                    <th>Projeto</th>
                                    <th>Módulo</th>
                                    <th>Descrição</th>
                                    <th>Status</th>
                                    <th>Solicitante</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $demandasRecentes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $demanda): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><strong><?php echo e($demanda->numero); ?></strong></td>
                                        <td><?php echo e($demanda->data->format('d/m/Y')); ?></td>
                                        <td><?php echo e($demanda->cliente->nome); ?></td>
                                        <td><?php echo e($demanda->projeto ? $demanda->projeto->nome : '-'); ?></td>
                                        <td><?php echo e($demanda->modulo); ?></td>
                                        <td><?php echo e(Str::limit($demanda->descricao, 50)); ?></td>
                                        <td>
                                            <span class="badge"
                                                style="background-color: <?php echo e($demanda->status->cor ?? '#6c757d'); ?>">
                                                <?php echo e($demanda->status->nome); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($demanda->solicitante->nome); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Nenhuma demanda encontrada</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="<?php echo e(route('demandas.index')); ?>" class="btn btn-primary">
                            Ver Todas as Demandas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/ronefalcao/Documents/IAS/demandas/resources/views/dashboard/index.blade.php ENDPATH**/ ?>