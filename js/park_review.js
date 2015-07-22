var rating_stars = new RatingStars($("#rating-stars"));
FormValidation($("#review-form"));

//include extra data in form for rating & park id
function addExtraData() {
	var rating = rating_stars.getRating();
	var rating_input = '<input name="review-rating" value="' + rating + '" type="hidden">';
	var park_id_input = '<input name="id" value="' + park_id + '" type="hidden">';
	$("#extra-data").innerHTML = (rating_input + park_id_input);
}