import jquery from 'jquery';

/* Časť funkcií pre jquery */
jquery(function() {
  
	/*Pre zobrazenie celého článku*/
	var cely = jquery('.cely_clanok');     //Nájdem doplnok textu
	cely.next().hide();               //Skryjem ho
	cely.click(function() {           //Pri kliku na článok
		jquery(this).fadeOut(200, function() {
			jquery(this).remove();             //Odstránim odkaz
		}).next().slideDown('slow');		//Skryjem samotný odkaz
		return false; 					        //Zakážem odkaz
	});

	/*Pre zobrazenie celého oznamu*/
	var cely = jquery('.cely_oznam');      //Nájdem doplnok textu
	var textC = cely.next().html();		//Najdem cely text
	var textU = cely.prev();          //Najdem upraveny text
	cely.next().hide().remove();      //Skryjem ho
	cely.click(function() {           //Pri kliku na článok
		textU.append('<span class="ost">' + textC + '</span>');
		var ost = jquery('.ost');
		ost.hide();
		jquery(this).fadeOut(200, function() {
			jquery(this).remove();             //Odstránim odkaz
  		ost.slideDown('slow');        //Skryjem samotný odkaz
		});			  
		return false;                   //Zakážem odkaz
	});

  jquery('.thumbnails').find('.thumb-a').each(function(){
    var el = jquery(this);
    el.click(function(){
      jquery('.thumb-a').removeClass('selected');
      jquery(this).addClass('selected');
    });
  });
  
});