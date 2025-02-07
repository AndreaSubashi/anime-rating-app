//handle update of rating dynamically
$(".update-rating-form").on("submit", function(event) {
    event.preventDefault();
    var form = $(this);
    var anime_id = form.find("input[name='anime_id']").val();
    var rating = form.find("input[name='rating']").val();

    $.ajax({
        url: "edit_rating.php",
        type: "POST",
        data: {
            anime_id: anime_id,
            rating: rating
        },
        success: function(response) {
            var data = JSON.parse(response);
            alert(data.message);
            if (data.status === "success") {
                //update displayed rating
                $("#rating-" + anime_id).val(rating);
            }
        }
    });
});

//handle update of comment dynamically
$(".update-comment-form").on("submit", function(event) {
    event.preventDefault();
    var form = $(this);
    var anime_id = form.find("input[name='anime_id']").val();
    var comment = form.find("textarea[name='comment']").val();

    $.ajax({
        url: "edit_comment.php",
        type: "POST",
        data: {
            anime_id: anime_id,
            comment: comment
        },
        success: function(response) {
            var data = JSON.parse(response);
            alert(data.message);
            if (data.status === "success") {
                //pdate displayed comment
                $("#comment-" + anime_id).text(comment);
            }
        }
    });
});

//handle delete action dynamically
$(".delete-anime-form").on("submit", function(event) {
    event.preventDefault();
    var form = $(this);
    var anime_id = form.find("input[name='anime_id']").val();

    $.ajax({
        url: "delete_anime.php",
        type: "POST",
        data: {
            anime_id: anime_id
        },
        success: function(response) {
            var data = JSON.parse(response);
            alert(data.message);
            if (data.status === "success") {
                //remove anime item from list
                $("#anime-" + anime_id).remove();
            }
        }
    });
});