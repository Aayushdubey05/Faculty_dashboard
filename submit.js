$(document).ready(function() {
    // Function to retrieve and display records
    function showRecords() {
        $.post('submit.php', { action: 'retrieve' }, function(data) {
            $('#records').empty();
            if (data.length === 0) {
                $('#records').append('<div>No records found.</div>');
            } else {
                $.each(data, function(index, record) {
                    $('#records').append('<div>' + 
                        'ID: ' + record.id + 
                        ' - Year: ' + record.year + 
                        ' - Month: ' + record.month + 
                        ' - Title: ' + record.title + 
                        ' - File: <a href="' + record.file_path + '" target="_blank">' + record.file_path + '</a>' + 
                        ' <button onclick="updateRecord(' + record.id + ')">Update</button>' + 
                        ' <button onclick="deleteRecord(' + record.id + ')">Delete</button>' + 
                        '</div>');
                });
            }
        }, 'json').fail(function() {
            $('#records').append('<div>Failed to load records.</div>');
        });
    }

    // Function to update a record
    window.updateRecord = function(id) {
        var new_title = prompt("Enter new title:");
        if (new_title) {
            $.post('submit.php', { action: 'update', id: id, new_title: new_title }, function(response) {
                alert(response);
                showRecords();
            }).fail(function() {
                alert("Failed to update record.");
            });
        }
    }

    // Function to delete a record
    window.deleteRecord = function(id) {
        if (confirm("Are you sure you want to delete this record?")) {
            $.post('submit.php', { action: 'delete', id: id }, function(response) {
                alert(response);
                showRecords();
            }).fail(function() {
                alert("Failed to delete record.");
            });
        }
    }

    // Form validation
    window.validateForm = function() {
        const year = document.getElementById('year').value;
        const month = document.getElementById('month').value;
        const title = document.getElementById('title').value;
        const file = document.getElementById('file').value;

        if (!year || isNaN(year) || year.length !== 4) {
            alert("Please enter a valid 4-digit year.");
            return false;
        }
        if (!month || isNaN(month) || month < 1 || month > 12) {
            alert("Please enter a valid month (1-12).");
            return false;
        }
        if (!title || title.trim() === "") {
            alert("Title cannot be empty.");
            return false;
        }
        if (!file) {
            alert("Please select a file to upload.");
            return false;
        }

        return true;
    }

    // Show records on page load
    showRecords();
});
