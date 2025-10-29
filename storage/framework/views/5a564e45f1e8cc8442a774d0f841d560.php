<?php $__env->startSection('title', 'Status'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-tag"></i> Status</h2>
        <a href="<?php echo e(route('status.create')); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Novo Status
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Cor</th>
                            <th>Ordem</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($status->id); ?></td>
                                <td><?php echo e($status->nome); ?></td>
                                <td>
                                    <span class="badge" style="background-color: <?php echo e($status->cor ?? '#6c757d'); ?>">
                                        <?php echo e($status->cor ?? 'N/A'); ?>

                                    </span>
                                </td>
                                <td><?php echo e($status->ordem); ?></td>
                                <td>
                                    <a href="<?php echo e(route('status.show', $status)); ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('status.edit', $status)); ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="<?php echo e(route('status.destroy', $status)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Deseja realmente excluir este status?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center">Nenhum status cadastrado</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                <?php echo e($statuses->links()); ?>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/ronefalcao/Documents/IAS/demandas/resources/views/status/index.blade.php ENDPATH**/ ?>