<?php $__env->startSection('title', 'Visualizar Demanda'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-eye"></i> Visualizar Demanda</h2>
        <a href="<?php echo e(route('demandas.index')); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Data:</strong> <?php echo e($demanda->data->format('d/m/Y')); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Status:</strong>
                        <span class="badge" style="background-color: <?php echo e($demanda->status->cor ?? '#6c757d'); ?>">
                            <?php echo e($demanda->status->nome); ?>

                        </span>
                    </p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Cliente:</strong> <?php echo e($demanda->cliente->nome); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Módulo:</strong> <?php echo e($demanda->modulo); ?></p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Responsável:</strong> <?php echo e($demanda->user->nome); ?></p>
                </div>
            </div>

            <div class="mb-3">
                <p><strong>Descrição:</strong></p>
                <div class="p-3 bg-light rounded">
                    <?php echo e($demanda->descricao); ?>

                </div>
            </div>

            <?php if($demanda->observacao): ?>
                <div class="mb-3">
                    <p><strong>Observação:</strong></p>
                    <div class="p-3 bg-light rounded">
                        <?php echo e($demanda->observacao); ?>

                    </div>
                </div>
            <?php endif; ?>

            <div class="d-flex gap-2">
                <a href="<?php echo e(route('demandas.edit', $demanda)); ?>" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Editar
                </a>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/ronefalcao/Documents/IAS/demandas/resources/views/demandas/show.blade.php ENDPATH**/ ?>