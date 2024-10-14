$(function () {
    initListing();
});


var deleteWithConfirmation = function (id, callback) {
    customConfirm({
        title: '<i class="fa fa-trash text-danger"></i>' + ' Mpd Stream?',
        message: 'Are you sure you want to delete?',
        callback: function (result) {
            if (!result) {
                return;
            }

            $.ajax({
                url: Router.route('content_decryption.destroy', {'stream_mpd': id}),
                method: 'DELETE',
                success: function () {
                    toastr['success']('Mpd Stream has been deleted.')
                    callback();
                },
                error: function (response) {
                    var r = response.responseJSON;
                    toastr['error'](r.message ? r.message : 'Mpd Stream failed to delete')
                    callback();
                }
            });
        }
    });
}

var start = function (id) {
    $.ajax({
        url: Router.route('content_decryption.start', {'stream_mpd': id}),
        method: 'POST',
        success: function () {
            toastr['success']('Stream is starting')
            $('#content_decryption_table').DataTable().ajax.reload(null, false);
        },
        error: function (response) {
            var r = response.responseJSON;
            toastr['error'](r.message ? r.message : 'Failed to start processing')
            $('#content_decryption_table').DataTable().ajax.reload(null, false);
        }
    });
}

var stop = function (id) {
    $.ajax({
        url: Router.route('content_decryption.stop', {'stream_mpd': id}),
        method: 'POST',
        success: function () {
            toastr['success']('Stream is stopping')
            $('#content_decryption_table').DataTable().ajax.reload(null, false);
        },
        error: function (response) {
            var r = response.responseJSON;
            toastr['error'](r.message ? r.message : 'Magnet Link failed to start processing')
            $('#content_decryption_table').DataTable().ajax.reload(null, false);
        }
    });
}

var initListing = function () {

    var table = $('#content_decryption_table');
    if (!table.length) {
        return;
    }

    table.DataTable({
        ajax: {
            url: Router.route('content_decryption.data'),
            method: 'GET'
        },
        serverSide: true,
        lengthMenu: [[25, 50, 100, 200, -1], [25, 50, 100, 200, "All"]],
        iDisplayLength: 100,
        order: [[1, 'desc']],
        columns: [
            {
                data: 'id',
                width: "22%",
                orderable: false,
                render: function (data, type, row) {
                    return view.renderString(`
            <div style="background-color: #e1f7d5; color: #2c3e50; padding: 5px 10px; border-radius: 5px; font-weight: bold; font-family: 'Courier New', monospace; font-size: 14px; text-align: center;">
                #{{ id }}
            </div>
        `, row);
                }
            },
            {
                data: 'name',
                orderable: false,
                render: function (data, type, row) {
                    return view.renderString(`
            <div style="padding: 5px; border-radius: 5px; background-color: #f1f8ff; color: #343a40; font-weight: bold; font-family: 'Arial', sans-serif;">
                {{ name }}
            </div>
        `, row);
                }
            },
            {
                data: 'status',
                orderable: false,
                render: function (data, type, row) {
                    let colors = {
                        'stopped': 'badge badge-danger',
                        'started': 'badge badge-success',
                    }

                    row.status_color = colors[data] ?? 'badge badge-success';  // Default color if status not found

                    return view.renderString(`
            <span class="{{ status_color }}" style="font-weight: bold; font-family: 'Verdana', sans-serif;">
                {{ status }}
            </span>
        `, row);
                }
            },
            {
                data: 'process_pid',
                orderable: false,
                render: function (data, type, row) {
                    return view.renderString(`
            <span class="badge badge-warning" style="font-size: 14px; padding: 5px; font-family: 'Courier New', monospace;">
                {{ process_pid }}
            </span>
        `, row);
                }
            },
            {
                data: 'server_name',
                orderable: false,
                render: function (data, type, row) {
                    return view.renderString(`
            <div style="color: #333; max-width: 250px; padding: 5px; border: 1px solid #ccc; border-radius: 5px; background-color: #eef2f5;">
                <span style="color: #007bff; font-weight: bold; font-family: 'Verdana', sans-serif;">
                    {{ server_name }}
                </span>
            </div>
        `, row);
                }
            },

            // {
            //     data: 'decryption_key',
            //     render: function (data, type, row) {
            //         return view.renderString('<span>{{ decryption_key }}</span>', row);
            //     }
            // },
            {
                data: 'rtmp_url',
                orderable: false,
                render: function (data, type, row) {
                    return view.renderString(`
                       <div style="color: #f7ffd9; max-width: 250px; max-height: 200px;  overflow-y: auto; word-wrap: break-word; padding: 5px; border: 1px solid #ddd; border-radius: 5px; background-color: #f0edd3;">
                <span  style=" color:dodgerblue; text-decoration: none; font-weight: bold; font-family: Arial, sans-serif;">
                    {{ rtmp_url }}
                </span>
            </div>
                    `, row);
                }
            },
            {
                data: 'url',
                orderable: false,
                render: function (data, type, row) {
                    return view.renderString(`
                       <div style="color: #f7ffd9; max-width: 250px; max-height: 200px;  overflow-y: auto; word-wrap: break-word; padding: 5px; border: 1px solid #ddd; border-radius: 5px; background-color: #f0edd3;">
                <span  style="color:dodgerblue; text-decoration: none; font-weight: bold; font-family: Arial, sans-serif;">
                    {{ url }}
                </span>
            </div>
                    `, row);
                }
            },
            {
                data: 'created_at',
                orderable: false,
                render: function (data, type, row) {
                    return view.renderString(`
            <div style="color: #6c757d; background-color: #f8f9fa; padding: 5px; border-radius: 5px; font-family: 'Arial', sans-serif; font-size: 13px;">
                <i class="far fa-clock"></i> {{ created_at }}
            </div>
        `, { created_at: formatDate(row.created_at) });
                }
            },
            {
                data: 'updated_at',
                orderable: false,
                render: function (data, type, row) {
                    return view.renderString(`
            <div style="color: #6c757d; background-color: #e9ecef; padding: 5px; border-radius: 5px; font-family: 'Arial', sans-serif; font-size: 13px;">
                <i class="far fa-clock"></i> {{ updated_at }}
            </div>
        `, { updated_at: formatDate(row.updated_at) });
                }
            },
            {
                width: '5%',
                className: 'actions',
                orderable: false,
                render: function (data, type, row) {
                    var start = view.renderString(
                        '<button type="button" class="btn btn-sm btn-outline-warning start" data-id="{{ id }}" title="Decrypt and Start Pushing"><i class="fas fa-play-circle"></i></button>',
                        row
                    );

                    var stop = view.renderString(
                        '<button type="button" class="btn btn-sm btn-outline-danger stop" data-id="{{ id }}" title="Stop"><i class="fas fa-stop-circle"></i></button>',
                        row
                    );

                    var editButton = view.renderString(
                        '<a href="{{ url }}" class="btn btn-sm btn-outline-primary mr-1" title="Edit"><i class="fa fa-pencil"></i></a>',
                        {'url': Router.route('content_decryption.edit', {'stream_mpd': row.id})}
                    );

                    var deleteButton = view.renderString(
                        '<button type="button" class="btn btn-sm btn-outline-danger delete" data-id="{{ id }}" title="Delete"><i class="fas fa-trash-alt"></i></button>',
                        row
                    );

                    return action_buttons.render(row, [start, stop, editButton, deleteButton]);
                }
            }
        ]
    });

    function formatDate (date) {
        return moment(date).utc().format('YYYY-MM-DD HH:mm:ss');
    }

    $(document).on('click', '.delete', function () {
        deleteWithConfirmation($(this).data('id'), function () {
            table.DataTable().ajax.reload(null, false);
        });
    });

    $(document).on('click', '.start', function () {
        start($(this).data('id'));
    });

    $(document).on('click', '.stop', function () {
        stop($(this).data('id'));
    });
};

