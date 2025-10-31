<?php $__env->startSection('title', 'Demandas'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-file-text"></i> Demandas</h2>
        <div>
            <a href="<?php echo e(route('demandas.exportar', request()->all())); ?>" target="_blank" class="btn btn-success me-2">
                <i class="bi bi-file-pdf"></i> Exportar PDF
            </a>
            <a href="<?php echo e(route('demandas.create')); ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nova Demanda
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('demandas.index')); ?>">
                <div class="row">
                    <div class="col-md-3">
                        <label for="cliente_id" class="form-label">Cliente</label>
                        <select class="form-select" id="cliente_id" name="cliente_id">
                            <option value="">Todos os clientes</option>
                            <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($cliente->id); ?>"
                                    <?php echo e(request('cliente_id') == $cliente->id ? 'selected' : ''); ?>>
                                    <?php echo e($cliente->nome); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="projeto_id" class="form-label">Projeto</label>
                        <select class="form-select" id="projeto_id" name="projeto_id">
                            <option value="">Todos os projetos</option>
                            <?php $__currentLoopData = $projetos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $projeto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($projeto->id); ?>"
                                    <?php echo e(request('projeto_id') == $projeto->id ? 'selected' : ''); ?>>
                                    <?php echo e($projeto->nome); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="status_id" class="form-label">Status</label>
                        <select class="form-select" id="status_id" name="status_id">
                            <option value="">Todos os status</option>
                            <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($status->id); ?>"
                                    <?php echo e(request('status_id') == $status->id ? 'selected' : ''); ?>>
                                    <?php echo e($status->nome); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
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
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $demandas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $demanda): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
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
                                <td>
                                    <a href="<?php echo e(route('demandas.show', $demanda)); ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if(!auth()->user()->isUsuario()): ?>
                                        <a href="<?php echo e(route('demandas.edit', $demanda)); ?>" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="<?php echo e(route('demandas.destroy', $demanda)); ?>" method="POST"
                                            class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Deseja realmente excluir esta demanda?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9" class="text-center">Nenhuma demanda encontrada</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                <?php echo e($demandas->links()); ?>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/ronefalcao/Documents/IAS/demandas/resources/views/demandas/index.blade.php ENDPATH**/ ?>