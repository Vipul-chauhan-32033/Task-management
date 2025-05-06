✅ AJAX Task Manager App (Laravel)
🎯 Goal
Make a small Task Manager app using Laravel + AJAX. Users can add, edit, delete, and view tasks without page reloads.


📌 Features (Step by Step)
1. Show All Tasks
Show tasks in a table (title, description, completed status)

Get task data using AJAX from route /fetch-tasks

Add search by title (AJAX)

Add pagination (AJAX)

2. Add New Task
A form with title and description

Submit using AJAX to POST /tasks

Show new task in list without reload

3. Edit Task
Each task has an Edit button

Open form in a popup or section

Submit changes via AJAX to PUT /tasks/{id}

4. Delete Task
Each task has a Delete button

Show confirm message

Delete using AJAX to DELETE /tasks/{id}

5. Mark as Done (Optional)
Use a checkbox or switch

Update task status via AJAX


🧱 Tech Used
Laravel 8 or 9

Blade + Bootstrap (UI)

jQuery AJAX for requests

MySQL for tasks (via Laravel migration)

Validation in Laravel controller

Routes in web.php

No page reload – all actions via AJAX



🏷 GitHub Topics
Add these in your GitHub repo settings:

laravel, ajax, crud, task-manager, jquery, bootstrap
