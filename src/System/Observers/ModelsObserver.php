<?php
 
namespace App\System\Observers;
 
class ModelsObserver {

    public function retrieved($model): void {}

    public function creating($model): void {
        if(\DB::connection($model->getConnection()->getName())->getSchemaBuilder()->hasColumn($model->getTable(), 'creation_user')) {
            $model->creation_user = \Auth::id() ?? 0;
        }
    }

    public function created($model): void {}

    public function updating($model): void {}

    public function updated($model): void {}

    public function saving($model): void {}

    public function saved($model): void {}

    public function deleting($model): void {
        $uses = class_uses($model);
        if(in_array("Illuminate\Database\Eloquent\SoftDeletes", $uses)) {
            $model->_delete(null, $model->getConnection()->getName());
        }
    }

    public function deleted($model): void {}

    public function trashed($model): void {}

    public function forceDeleting($model): void {}

    public function forceDeleted($model): void {}

    public function restoring($model): void {}

    public function restored($model): void {}

    public function replicating($model): void {}
}