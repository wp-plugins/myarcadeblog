<script type="text/javascript">

function myarcade_chkImportCustom() {
  if (document.FormCustomGame.importgame.value == "") {
    alert("<?php _e("No game file added!", MYARCADE_TEXT_DOMAIN); ?>");
    return false;
  }  
  if (document.FormCustomGame.importthumb.value == "") {
    alert("<?php _e("No thumbnail added!", MYARCADE_TEXT_DOMAIN); ?>");
    return false;
  }   
  if (document.FormCustomGame.gamename.value == "") {
    alert("<?php _e("Game name not set!", MYARCADE_TEXT_DOMAIN); ?>");
    document.FormCustomGame.gamename.focus();
    return false;
  }
  if (document.FormCustomGame.gamedescr.value == "") {
    alert("<?php _e("There is no game description!", MYARCADE_TEXT_DOMAIN); ?>");
    document.FormCustomGame.gamedescr.focus();
    return false;
  }

  var categs = false;
  for(var i = 0; i < document.FormCustomGame.elements.length - 1; i++) {
    if( (document.FormCustomGame.elements[i].type == "checkbox") && (document.FormCustomGame.elements[i].checked == true)) {
      categs = true;
      break;
    }
  }

  if (categs == false) {
    alert("<?php _e("Select at least one category!", MYARCADE_TEXT_DOMAIN);?>");
    return false;
  }

  return true;
} // END - myarcade_chkImportCustom



/* Import method selection */
jQuery(document).ready(function() {
  jQuery('#importibparcade').hide();  
  jQuery('#importembedif').hide();
  jQuery('#importmethod').change( function() {
    jQuery('#filename').html('');
    jQuery('#filenametar').html('');
    jQuery('#gamewidth').val('');
    jQuery('#gameheight').val('');
    jQuery('#gamename').val('');
    jQuery('#importgame').val('');
    jQuery('#importtype').val(''); 
    jQuery('#importscreen1').val('');
    jQuery('#importscreen2').val('');
    jQuery('#importscreen3').val('');
    jQuery('#importscreen4').val('');
    jQuery('#lbenabled').val('');
    jQuery('#highscoretype').val('');
    jQuery('#slug').val('');   
    
    switch (this.value) {
      case 'importibparcade': {
        jQuery('#importswfdcr').hide();
        jQuery('#importembedif').hide();
        jQuery('#thumbform').hide();
        jQuery('#importibparcade').fadeIn('slow');        
      }
      break;
      
      case 'importswfdcr': {
        jQuery('#importibparcade').hide();
        jQuery('#importembedif').hide();
        jQuery('#importswfdcr').fadeIn('slow');
        jQuery('#thumbform').fadeIn('slow');
      }
      break;
      
      case 'importembedif': {
        jQuery('#importibparcade').hide();
        jQuery('#importswfdcr').hide();
        jQuery('#importembedif').fadeIn('slow'); 
        jQuery('#thumbform').fadeIn('slow');
               
      }
      break;
    }
  });
  jQuery('#importmethod').change();
}); 
  
/* SWF Upload Handler */  
jQuery(function() {
// Formular abschicken
jQuery('#uploadFormSWF').submit(function(data) {
  // Ajax Loader anzeigen
  jQuery('#loader').html('<img src="<?php echo WP_PLUGIN_URL; ?>/myarcadeblog/modules/loading.gif" border="0" />');
  // abschickendes Formular angeben
  var submittingForm = jQuery('#uploadFormSWF');
  // eindeutigen iFrame Namen generieren
  var date = new Date();
  var ms = date.getMilliseconds();
  var frameName = ("upload" + ms);
  // iFrame setzen
  var uploadFrame = jQuery('<iframe name="' + frameName + '"></iframe>');
  // iFrame verstecken
  uploadFrame.css("display", "none");
  // iFrame Inhalt laden
  uploadFrame.load(function(data) {
    // Timeout festlegen
    setTimeout(function() {
      // Rueckgabewert abfragen
      var json_data = uploadFrame.contents().find('#result_swf').html();        
      var json_obj = jQuery.parseJSON(json_data);
      if (json_obj != null) {
        if ( json_obj.error == 'none') {        
          // Formulardaten ausfüllen
          jQuery('#loader').html('');
          jQuery('#filename').html('<strong>' + json_obj.name + '</strong> - <i>' + json_obj.info_dim + '</i>');
          jQuery('#gamewidth').val(json_obj.width);
          jQuery('#gameheight').val(json_obj.height);
          jQuery('#gamename').val(json_obj.realname);
          jQuery('#importgame').val(json_obj.location_url);
          jQuery('#importtype').val(json_obj.type);
        }
        else {
          jQuery('#filename').html(json_obj.error);
        }
        
        uploadFrame.remove();
      }     
    }, 3000);
  });
  // iFrame zum Body hinzufuegen
  jQuery('body:first').append(uploadFrame);
  // Formular target zum iFrame setzen
  submittingForm.attr('target', frameName);
});

jQuery('#swfupload').click(function() {
  // Formular abschicken
  jQuery('#uploadFormSWF').submit();
  });
});  

/* Thumb Upload Handler */  
jQuery(function() {
  jQuery('#uploadFormTHUMB').submit(function(data) {
    jQuery('#loaderthumb').html('<img src="<?php echo WP_PLUGIN_URL; ?>/myarcadeblog/modules/loading.gif" border="0" />');
      var submittingForm = jQuery('#uploadFormTHUMB');
      var date = new Date();
      var ms = date.getMilliseconds();
      var frameName = ("thumb" + ms);
      var uploadFrame = jQuery('<iframe name="' + frameName + '"></iframe>');
      uploadFrame.css("display", "none");
      uploadFrame.load(function(data) {
        setTimeout(function() {
          var json_data = uploadFrame.contents().find('#result_thumb').html();          
          var json_obj = jQuery.parseJSON(json_data);   
          if (json_obj != null) {   
            if ( json_obj.error == 'none') {        
              jQuery('#filenamethumb').html('<img src="' + json_obj.thumb_url + '" alt=""  />');
              jQuery('#importthumb').val(json_obj.thumb_url);
            }
            else {
              jQuery('#filenamethumb').html(json_obj.error);
            }
            
            uploadFrame.remove();
            jQuery('#loaderthumb').html('');            
          }        
        
      }, 3000);
    });
    
    jQuery('body:first').append(uploadFrame);
    submittingForm.attr('target', frameName);
  });

  jQuery('#thumbupload').click(function() {
    jQuery('#uploadFormTHUMB').submit();
  });
});


/* Screen Upload Handler */  
jQuery(function() {
  jQuery('#uploadFormSCREEN').submit(function(data) {
    jQuery('#loaderscreen').html('<img src="<?php echo WP_PLUGIN_URL; ?>/myarcadeblog/modules/loading.gif" border="0" />');
      var submittingForm = jQuery('#uploadFormSCREEN');
      var date = new Date();
      var ms = date.getMilliseconds();
      var frameName = ("screen" + ms);
      var uploadFrame = jQuery('<iframe name="' + frameName + '"></iframe>');
      uploadFrame.css("display", "none");
      uploadFrame.load(function(data) {
        setTimeout(function() {
          var json_data = uploadFrame.contents().find('#result_screen').html();          
          var json_obj = jQuery.parseJSON(json_data);
          var output_string = '';
          
          if (json_obj != null) {      
            if ( json_obj.error == 'none') {
              for (var i=0;  i<=3; i++) {
                if (json_obj.screen_name[i] != '') {
                  var x = i + 1;
                  output_string += '<strong>Screen ' + x + ': ' + json_obj.screen_name[i] + '</strong><br />';
                  jQuery('#importscreen' + x).val(json_obj.screen_url[i]);
                }
                else {
                  output_string += json_obj.screen_error[i] + '<br />';
                }
              }              
              jQuery('#filenamescreen').html(output_string);
            }
            else {
              jQuery('#filenamescreen').html(json_obj.error);
            }
            
            uploadFrame.remove();
            jQuery('#loaderscreen').html('');            
          } 
        }, 3000);
      });
    
      jQuery('body:first').append(uploadFrame);
      submittingForm.attr('target', frameName);
    });
  
  jQuery('#screenupload').click(function() {
    jQuery('#uploadFormSCREEN').submit();
  });
});
</script>  