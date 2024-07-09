<?php
return [
    "task" => "Task",
    "tasks" => "Tasks",
    "members" => "Members",
    'title' => 'Tasks',
    'creator' => 'Creator',
    'add_task' => 'Add New Task',
    'add_member' => 'Add New Member',
    'edit_task' => 'Edit :title',
    'task_details' => 'Details of :title',
    'dashboard' => 'Task Manager',
    "link" => "Link",
    'doc_link' => "Deal or Contact or Item",
    'notifications' => [
        'task_created'  => 'You created :task',
        'task_updated'  => ':user updated :task',
        'task_assigned' => ':task assigned to you',
        'task_status'   => ':user changed task :task status to :status',
        'task_comment'  => ':user commented on :task',
        'task_reminder' => ':user set reminder for task :task',
        'task_reminder_alert' => 'This is a task reminder alert for your task :task',
        'task_fields' => [
            'title' => ':user updated title for task :task',
            'priority' => ':user updated priority for task :task',
            'description' => ':user updated description for task :task',
            'files' => ':user updated attachments for task :task',
            'status_id' => ':user updated status for task :task',
            'delivery_date' => ':user updated delivery date for task :task',
            'reminder' => ':user updated reminder for task :task',
        ],
    ],
    'form' => [
        'are_you_sure_want_to_assign_task_to_user' => "Are you sure want to assign tasks to user "
    ]
];
