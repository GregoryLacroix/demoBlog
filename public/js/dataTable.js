$(document).ready(function() {
    $('#table-backoffice').DataTable({
        language: {
            url: '/js/dataTables.french.json'
        },
        "aoColumnDefs": [
            { 'bSortable': false, 'aTargets': [ 1,2,5,6 ] }
        ]
    });

    $('#table-category').DataTable({
        language: {
            url: '/js/dataTables.french.json'
        },
        "aoColumnDefs": [
            { 'bSortable': false, 'aTargets': [ 1,3 ] }
        ]
    });
});
