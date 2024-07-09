<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;

// Admins
Breadcrumbs::for('users', function ($trail) {
    $trail->push('Users List', route('users'));
});

Breadcrumbs::for('users.create', function ($trail) {
    $trail->parent('users');
    $trail->push('Add New User', route('users.create'));
});

Breadcrumbs::for('users.edit', function ($trail,$user) {
    $trail->parent('users');
    $trail->push('Edit '.$user->username, route('users.edit',$user));
});

Breadcrumbs::for('tools', function ($trail) {
    $trail->push('Tools', route('tools.index'));
});

/*** Issue ***/
Breadcrumbs::for('issues', function ($trail) {
    $trail->push(__('ui.menu.support').' '.trans_choice('ui.ticket',2), route('supports'));
});

Breadcrumbs::for('issues.create', function ($trail) {
    $trail->parent('issues');
    $trail->push(__('ui.add').' '.__('ui.new').' '.trans_choice('ui.ticket',1), route('supports.create'));
});

Breadcrumbs::for('issues.edit', function ($trail) {
    $trail->parent('issues');
    $trail->push(__('ui.edit').' '.trans_choice('ui.ticket',1), route('supports'));
});
/*** Issue END ***/

Breadcrumbs::for('tm_tasks.manage', function ($trail) {
    $trail->parent('dealers.dashboard');
    $trail->push(__('task.title'), route('task-manager.tasks.index'));
});

Breadcrumbs::for('tm_tasks.create', function ($trail) {
    $trail->parent('tm_tasks.manage');
    $trail->push(__('task.add_task'), route('task-manager.tasks.create'));
});

Breadcrumbs::for('tm_tasks.details', function ($trail, $task) {
    $trail->parent('tm_tasks.manage');
    $trail->push(__('task.task_details', ['title' => $task->title]), route('task-manager.tasks.show', $task));
});

Breadcrumbs::for('tm_tasks.edit', function ($trail, $task) {
    $trail->parent('tm_tasks.manage');
    $trail->push(__('task.edit_task', ['title' => $task->title]), route('task-manager.tasks.edit', $task));
});

Breadcrumbs::for('tm_members.manage', function ($trail) {
    $trail->parent('dealers.dashboard');
    $trail->push(__('task.members'), route('task-manager.members.index'));
});

Breadcrumbs::for('tm_members.create', function ($trail) {
    $trail->parent('tm_members.manage');
    $trail->push(__('task.add_member'), route('task-manager.members.create'));
});

Breadcrumbs::for('tm_members.edit', function ($trail, $member) {
    $trail->parent('tm_members.manage');
    $trail->push(sprintf("%s %s", __('ui.edit'), ucfirst($member->username)), route('task-manager.members.edit', $member));
});
