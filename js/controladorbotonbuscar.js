$(document).ready(function(){
	$('#f0').on('click',function(){
		$('.formulario').addClass('hide').removeClass('show');
		$('#t0').addClass('show').removeClass('hide');
		 jQuery.ajax({
		        url: 'itra.php',
		        type: 'POST',
		        data: {
		            txt: 0,
		        },
		        dataType : 'json',
		        success: function(data, textStatus, xhr) {
		            console.log(data);
		        },
		        error: function(xhr, textStatus, errorThrown) {
		            console.log(textStatus.reponseText);
		        }
		    });
	});
	$('#f1').on('click',function(){
		$('.formulario').addClass('hide').removeClass('show');
		$('#t1').addClass('show').removeClass('hide');
		jQuery.ajax({
	        url: 'itra.php',
	        type: 'POST',
	        data: {
	            txt: 1,
	        },
	        dataType : 'json',
	        success: function(data, textStatus, xhr) {
	            console.log(data);
	        },
	        error: function(xhr, textStatus, errorThrown) {
	            console.log(textStatus.reponseText);
	        }
	    });
	});
	$('#f2').on('click',function(){
		$('.formulario').addClass('hide').removeClass('show');
		$('#t2').addClass('show').removeClass('hide');
		jQuery.ajax({
	        url: 'itra.php',
	        type: 'POST',
	        data: {
	            txt: 2,
	        },
	        dataType : 'json',
	        success: function(data, textStatus, xhr) {
	            console.log(data);
	        },
	        error: function(xhr, textStatus, errorThrown) {
	            console.log(textStatus.reponseText);
	        }
	    });
	});
	$('#f3').on('click',function(){
		$('.formulario').addClass('hide').removeClass('show');
		$('#t3').addClass('show').removeClass('hide');
		jQuery.ajax({
	        url: 'itra.php',
	        type: 'POST',
	        data: {
	            txt: 3,
	        },
	        dataType : 'json',
	        success: function(data, textStatus, xhr) {
	            console.log(data);
	        },
	        error: function(xhr, textStatus, errorThrown) {
	            console.log(textStatus.reponseText);
	        }
	    });
	});
	$('#f4').on('click',function(){
		$('.formulario').addClass('hide').removeClass('show');
		$('#t4').addClass('show').removeClass('hide');
		jQuery.ajax({
	        url: 'itra.php',
	        type: 'POST',
	        data: {
	            txt: 4,
	        },
	        dataType : 'json',
	        success: function(data, textStatus, xhr) {
	            console.log(data);
	        },
	        error: function(xhr, textStatus, errorThrown) {
	            console.log(textStatus.reponseText);
	        }
	    });
	});
	$('#f5').on('click',function(){
		$('.formulario').addClass('hide').removeClass('show');
		$('#t5').addClass('show').removeClass('hide');
		jQuery.ajax({
	        url: 'itra.php',
	        type: 'POST',
	        data: {
	            txt: 5,
	        },
	        dataType : 'json',
	        success: function(data, textStatus, xhr) {
	            console.log(data);
	        },
	        error: function(xhr, textStatus, errorThrown) {
	            console.log(textStatus.reponseText);
	        }
	    });
	});
	$('#f6').on('click',function(){
		$('.formulario').addClass('hide').removeClass('show');
		$('#t6').addClass('show').removeClass('hide');
		jQuery.ajax({
	        url: 'itra.php',
	        type: 'POST',
	        data: {
	            txt: 6,
	        },
	        dataType : 'json',
	        success: function(data, textStatus, xhr) {
	            console.log(data);
	        },
	        error: function(xhr, textStatus, errorThrown) {
	            console.log(textStatus.reponseText);
	        }
	    });
	});
	$('#f7').on('click',function(){
		$('.formulario').addClass('hide').removeClass('show');
		$('#t7').addClass('show').removeClass('hide');
		jQuery.ajax({
	        url: 'itra.php',
	        type: 'POST',
	        data: {
	            txt: 7,
	        },
	        dataType : 'json',
	        success: function(data, textStatus, xhr) {
	            console.log(data);
	        },
	        error: function(xhr, textStatus, errorThrown) {
	            console.log(textStatus.reponseText);
	        }
	    });
	});
	$('#f8').on('click',function(){
		$('.formulario').addClass('hide').removeClass('show');
		$('#t8').addClass('show').removeClass('hide');
		jQuery.ajax({
	        url: 'itra.php',
	        type: 'POST',
	        data: {
	            txt: 8,
	        },
	        dataType : 'json',
	        success: function(data, textStatus, xhr) {
	            console.log(data);
	        },
	        error: function(xhr, textStatus, errorThrown) {
	            console.log(textStatus.reponseText);
	        }
	    });
	});
	$('#f9').on('click',function(){
		$('.formulario').addClass('hide').removeClass('show');
		$('#t9').addClass('show').removeClass('hide');
		jQuery.ajax({
	        url: 'itra.php',
	        type: 'POST',
	        data: {
	            txt: 9,
	        },
	        dataType : 'json',
	        success: function(data, textStatus, xhr) {
	            console.log(data);
	        },
	        error: function(xhr, textStatus, errorThrown) {
	            console.log(textStatus.reponseText);
	        }
	    });
	});
	$('#f10').on('click',function(){
		$('.formulario').addClass('hide').removeClass('show');
		$('#t10').addClass('show').removeClass('hide');
		jQuery.ajax({
	        url: 'itra.php',
	        type: 'POST',
	        data: {
	            txt: 10,
	        },
	        dataType : 'json',
	        success: function(data, textStatus, xhr) {
	            console.log(data);
	        },
	        error: function(xhr, textStatus, errorThrown) {
	            console.log(textStatus.reponseText);
	        }
	    });
	});
	$('#f11').on('click',function(){
		$('.formulario').addClass('hide').removeClass('show');
		$('#t11').addClass('show').removeClass('hide');
		jQuery.ajax({
	        url: 'itra.php',
	        type: 'POST',
	        data: {
	            txt: 11,
	        },
	        dataType : 'json',
	        success: function(data, textStatus, xhr) {
	            console.log(data);
	        },
	        error: function(xhr, textStatus, errorThrown) {
	            console.log(textStatus.reponseText);
	        }
	    });
	});
});