jQuery.texyla.setDefaults({
	youtubeMakro: "[* youtube:%var% *]"
});

jQuery.texyla.addWindow("youtube", {
	createContent: function () {
		var el = jQuery(
			"<div><form><div>" +
			'<label>' + this.lng.youtubeUrl + '<br>' +
			'<input type="text" size="35" class="key">' +
			"</label><br><br>" +
			this.lng.youtubePreview + '</div>' +
			'<div class="thumb"></div>' +
			"</form></div>"
		);

		el.find(".key").bind("keyup change", function () {
			var val = this.value;
			var key = "";

			if (val.substr(0, 8) === "https://") {
				var res = val.match("[?&]v=([a-zA-Z0-9_-]+)");
				if (res) key = res[1];
			} else {
				key = val;
			}

			jQuery(this).data("key", key);

			el.find(".thumb").html(//https://youtu.be/SMVKEv3jcio
				'<img src="//i.ytimg.com/vi/' + key + '/mqdefault.jpg" width="120" height="90">'
			); 
		});

		return el;
	},

	action: function (el) {
		var txt = this.expand(this.options.youtubeMakro, el.find(".key").data("key"));
		this.texy.update().replace(txt);
	},

	dimensions: [410, 410]
});

jQuery.texyla.addStrings("cs", {
});