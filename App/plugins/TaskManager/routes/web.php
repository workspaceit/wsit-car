<?php

use Illuminate\Support\Facades\Route;

//Route::get('/dashboard', "TaskManagerController@board")->name("board");

Route::resources(['tasks' => TasksController::class]);
Route::resources(['members' => MembersController::class]);

Route::post('members/delete-image', "MembersController@deleteImage")->name("members.deleteImage");
Route::post('/ajaxUpdateStatus', "TasksController@ajaxUpdateStatus")->name("ajaxUpdateStatus");
Route::post('/modify/reminder', "TasksController@modifyTaskReminder")->name("tasks.modify.reminder");
Route::post('tasks/delete-attachments', "TasksController@deleteAttachments")->name("tasks.deleteAttachments");
Route::get('tasks/linked-links/fetch', "TasksController@fetchIssues")->name("tasks.issues.fetch");
Route::delete('tasks/linked-links/{link}/destroy', "TasksController@destroyLinkedLink")->name("tasks.links.destroy");

Route::post('/addTaskComment', "TasksController@addTaskComment")->name("addTaskComment");
Route::post('/updateTaskComment', "TasksController@updateTaskComment")->name("updateTaskComment");
Route::post('/deleteTaskComment', "TasksController@deleteTaskComment")->name("deleteTaskComment");

Route::post('/bulk-assign', 'TasksController@bulkAssign')->name('bulkAssign');
Route::delete('/destroy-multiple', 'TasksController@destroyMultiple')->name('destroyMultiple');
