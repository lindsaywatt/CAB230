var ajax = new Ajax();

var delete_buttons = $$(".delete-button");
for (var i = 0; i < delete_buttons.length; i++) {
	delete_buttons[i].onclick = function(e) {
		ajax.post("delete_review.php", {review_id: this.id},
			function(response) {
				if (response.responseText !== "error") {
					location.reload();
				} else {
					alert(response.responseText);
				}
			},
			function(response) {
				alert(response.responseText);
			}
		);
	};
}