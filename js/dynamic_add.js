// Handle form submission with AJAX
$(".add-to-list-form").on("submit", function(event) {
    event.preventDefault();
    var form = $(this);
    var button = form.find("button");
    var originalText = button.text();
    button.prop('disabled', true).text('Adding...'); // Show loading state

    $.ajax({
        url: form.attr("action"),
        type: "POST",
        data: form.serialize(),
        success: function(response) {
            var data = JSON.parse(response);
            if (data.status === "success") {
                alert(data.message);
                form.html('<p>Already added</p>'); // Replace form with message
            } else {
                alert(data.message);
                button.prop('disabled', false).text(originalText);
            }
        },
        error: function() {
            alert("An error occurred. Please try again.");
            button.prop('disabled', false).text(originalText);
        }
    });
});