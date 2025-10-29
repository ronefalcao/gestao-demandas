<?php $__env->startSection('title', 'Usuários'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people-fill"></i> Usuários</h2>
        <a href="<?php echo e(route('users.create')); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Novo Usuário
        </a>
    </div>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>Tipo</th>
                            <th>Criado em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($user->id); ?></td>
                                <td><?php echo e($user->nome); ?></td>
                                <td><?php echo e($user->email); ?></td>
                                <td><?php echo e($user->telefone ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge <?php echo e($user->tipo === 'administrador' ? 'bg-danger' : 'bg-primary'); ?>">
                                        <?php echo e(ucfirst($user->tipo)); ?>

                                    </span>
                                </td>
                                <td><?php echo e($user->created_at->format('d/m/Y')); ?></td>
                                <td>
                                    <a href="<?php echo e(route('users.show', $user)); ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('users.edit', $user)); ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if($user->id !== auth()->id()): ?>
                                        <form action="<?php echo e(route('users.destroy', $user)); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Deseja realmente excluir este usuário?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center">Nenhum usuário cadastrado</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                <?php echo e($users->links()); ?>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/ronefalcao/Documents/IAS/demandas/resources/views/users/index.blade.php ENDPATH**/ ?>