<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Task Management AJAX CRUD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .alert-message {
            color: red;
        }

        .pagination {
            margin-top: 20px;
        }

        .page-item.disabled .page-link {
            pointer-events: none;
            opacity: 0.6;
        }

        .loading {
            display: none;
            color: #007bff;
        }

        .error-message {
            color: red;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4 d-flex justify-content-center">Task Management (AJAX CRUD)</h1>
        <div class="mb-3 d-flex justify-content-center align-items-center">
            <input type="text" id="search" class="form-control me-2" placeholder="Search tasks..."
                style="max-width: 300px;">
            <div class="loading">Loading...</div>
        </div>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#taskModal">Add
            Task</button>
        <div class="error-message" id="errorMessage"></div>
        <table class="table table-bordered" id="tasksTable">
            <thead>
                <tr>
                    <th width="10%">Completed</th>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tasksBody"></tbody>
        </table>
        <div id="pagination" class="d-flex justify-content-center"></div>
    </div>

    <div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskModalLabel">Add Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="taskForm">
                        @csrf
                        <input type="hidden" id="task_id">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                            <span class="alert-message" id="title_error"></span>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" id="saveButton">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            let currentPage = 1;
            let perPage = 5;
            let searchQuery = '';
            let isLoading = false;

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            function toggleLoading(show) {
                $('.loading').css('display', show ? 'block' : 'none');
            }

            function showError(message) {
                $('#errorMessage').text(message).show();
                setTimeout(() => $('#errorMessage').fadeOut(), 3000);
            }

            function loadTasks(page = 1, search = '') {
                if (isLoading) return;
                isLoading = true;
                toggleLoading(true);

                $.ajax({
                    url: "{{ route('tasks.get') }}",
                    method: 'GET',
                    data: {
                        page: page,
                        per_page: perPage,
                        search: search
                    },
                    success: function (response) {
                        let tasks = response.data || [];
                        let tbody = '';
                        tasks.forEach(task => {
                            const checked = task.completed === 'pending' ? '' : 'checked';
                            tbody += `
                    <tr>
                        <td>
                            <input type="checkbox" class="status-checkbox" data-id="${task.id}" ${checked}>
                        </td>
                        <td>${task.id}</td>
                        <td>${escapeHtml(task.title)}</td>
                        <td>${escapeHtml(task.description || '')}</td>
                        
                        <td>
                            <button class="btn btn-sm btn-warning edit-task" data-id="${task.id}">Edit</button>
                            <button class="btn btn-sm btn-danger delete-task" data-id="${task.id}">Delete</button>
                        </td>
                    </tr>
                `;
                        });

                        $('#tasksBody').html(tbody);

                        const currentPage = Number(response.current_page || 1);
                        const lastPage = Number(response.last_page || 1);
                        let pagination = '';

                        if (lastPage > 1) {
                            pagination += `
                    <nav>
                        <ul class="pagination">
                            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                                <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
                            </li>
                `;
                            for (let i = 1; i <= lastPage; i++) {
                                pagination += `
                        <li class="page-item ${currentPage === i ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `;
                            }
                            pagination += `
                            <li class="page-item ${currentPage === lastPage ? 'disabled' : ''}">
                                <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
                            </li>
                        </ul>
                    </nav>
                `;
                        }

                        $('#pagination').html(pagination);
                    },
                    error: function () {
                        showError('Failed to load tasks. Please try again.');
                    },
                    complete: function () {
                        isLoading = false;
                        toggleLoading(false);
                    }
                });
            }

            function escapeHtml(text) {
                return $('<div>').text(text).html();
            }

            loadTasks();

            let searchTimeout;
            $('#search').on('keyup', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    searchQuery = $(this).val();
                    currentPage = 1;
                    loadTasks(currentPage, searchQuery);
                }, 300);
            });

            $(document).on('click', '.pagination .page-link', function (e) {
                e.preventDefault();
                let page = $(this).data('page');
                if (page && !$(this).parent().hasClass('disabled')) {
                    currentPage = page;
                    loadTasks(currentPage, searchQuery);
                }
            });

            function resetForm() {
                $('#taskForm')[0].reset();
                $('#task_id').val('');
                $('#taskModalLabel').text('Add Task');
                $('#saveButton').text('Save');
                $('.alert-message').text('');
            }

            $('#taskModal').on('show.bs.modal', function () {
                resetForm();
            });

            $('#taskForm').on('submit', function (e) {
                e.preventDefault();
                let id = $('#task_id').val();
                let url = id ? `/tasks/${id}` : "{{ route('tasks.store') }}";
                let method = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    method: method,
                    data: $(this).serialize(),
                    success: function (response) {
                        $('#taskModal').modal('hide');
                        loadTasks(currentPage, searchQuery);
                        alert(response.success);
                    },
                    error: function (xhr) {
                        let errors = xhr.responseJSON.errors || {};
                        $('#title_error').text(errors.title ? errors.title[0] : '');
                        showError('Failed to save task. Please check the form.');
                    }
                });
            });

            $(document).on('click', '.edit-task', function () {
                let id = $(this).data('id');
                $.ajax({
                    url: `/tasks/${id}`,
                    method: 'GET',
                    success: function (task) {
                        $('#taskModal').on('show.bs.modal', function () {
                            $('#task_id').val(task.id);
                            $('#title').val(task.title);
                            $('#description').val(task.description);
                            $('#taskModalLabel').text('Edit Task');
                            $('#saveButton').text('Update');
                        });

                        $('#taskModal').modal('show');
                    },
                    error: function () {
                        showError('Failed to load task details.');
                    }
                });
            });

            $(document).on('click', '.delete-task', function () {
                if (confirm('Are you sure you want to delete this task?')) {
                    let id = $(this).data('id');
                    $.ajax({
                        url: `/tasks/${id}`,
                        method: 'DELETE',
                        success: function (response) {
                            loadTasks(currentPage, searchQuery);
                            alert(response.success);
                        },
                        error: function () {
                            showError('Failed to delete task.');
                        }
                    });
                }
            });

            $(document).on('change', '.status-checkbox', function () {
                let checkbox = $(this);
                let id = checkbox.data('id');
                let status = checkbox.is(':checked') ? 'completed' : 'pending';

                $.ajax({
                    url: `/tasks/${id}/status`,
                    method: 'PUT',
                    data: { status: status },
                    success: function (response) {
                        loadTasks(currentPage, searchQuery);
                        alert(response.success);
                    },
                    error: function () {
                        showError('Failed to update status.');
                        checkbox.prop('checked', !checkbox.is(':checked'));
                    }
                });
            });

        });
    </script>
</body>

</html>