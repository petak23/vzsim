$(function () {
	$(".texyla").texyla({
		toolbar: [
			'h1', 'h2', 'h3', 'h4',
			null,
			'bold', 'italic',
			null,
			'center', ['left', 'right', 'justify'],
			null,
			'ul', 'ol', ["olAlphabetSmall", "olAlphabetBig", "olRomans", "olRomansSmall"],
			null,
			{ type: "label", text: "Vlož"}, 'link', 'img', 'table', 'emoticon', 'symbol',
			null,
			'color', 'textTransform',
			null,
			'files', 'youtube', 'gravatar',
			null,
			'div', ['html', 'blockquote', 'text', 'comment'],
			null,
			'code',	['codeHtml', 'codeCss', 'codeJs', 'codePhp', 'codeSql'], 'codeInline',
			null,
			/*{ type: "label", text: "Ostatné"}, [*/'sup', 'sub', 'del', 'acronym', 'hr', 'notexy'/*, 'web']*/

		],
		texyCfg: "admin",
		bottomLeftToolbar: ['edit', 'preview', 'htmlPreview'],
    bottomRightPreviewToolbar: [],
		buttonType: "span",
		tabs: true,
    language: "sk"
	});

	$.texyla({
		buttonType: "button"
	});
});