//backyard 1
/*!
 * coloursave v3 (130919)
 */
/**
 * Posílátko pro libovolný element, který umožňuje držet uživatelem zadávaný obsah.
 * Pošle při defocus/změně jeho obsah s´případně s kontextem na definované API URL.
 * S tím, že automaticky mění barvu podle stavu odesílání.
 * 
 * All class="coloursave" must have `name' defined.
 * class="required" is possible and if empty the colour of its parent is defined by .colourSaveHighlight
 * Therefore it is recommended to contain the element and its neighborhood into a <span/> or <div/> container to prevent other text being highlighted.
 * Only `a' and `button' elements must have `id' defined. And its value is passed in data-coloursave-arg. data-parent may be used for these two elements, as well.
 * 
 * URL of the API may either be defined globally by `var apiUrlColoursave' 
 * or may be modified on element level in data attribute coloursave-api
 * 
 * When the value of element is changed by user, this script POSTs following parameters:
action:api_update  //constant. API should deal with CRUD (create-read-update-delete) operations. The coloursave script handles just the UPDATE.
api_arg:{"relation_task":"Umí Telefónica kombinovaný SMS+MMS kanál? A co když lidé odpoví MMS? Smlouva?\n....\nJak to vypada s STK deploymentem?"} //name of the element and its value
api_context:{"project_id":3,"person_id":15,"relation_id":15} //optional information set in the data attribute coloursave-context. Equals to null if not set. @TODO - anebo nepošle nic?
eid:92             //if not set in data attribute eid, it is equal to zero
 * 
 * If parent element should be disabled instead of the changed element, put parent's id into data attribute parent
 * 
 * If the API response contain "error":"Anything to alert to the user." , then the string is alert()ed.
 */
/**
 * @TODO 1 - přepsat tak, aby funkce nebyly v konfliktu s původním kódem a refaktorovat ve Stakan1  
 * 
 */
'use strict';
//TBD: Use single quote instead of double quotes.

/**
 * Init
 */

var apiUrlErrorLog = 'http://free.t-mobile.cz/check13stage/api/v1/error_log/';
//var relativePathToBackyardJs = 'lib/backyard/deploy/backyard/js';
var relativePathToBackyardJs = '../deploy/backyard/js';

var localisationString = new Array();
localisationString['fill_in_red'] = 'Vyplňte červené pole'; // @TODO - customize někde v config
localisationString["sent_to_server"] = 'Odesláno na server';// @TODO - customize někde v config

if(typeof(apiUrlColoursave) === 'undefined') var apiUrlColoursave = relativePathToBackyardJs + '/' + 'dummy.json';

/**
 * /Init
 */

/* NOTE:
$(selector).live(events, data, handler);                // jQuery 1.3+
$(document).delegate(selector, events, data, handler);  // jQuery 1.4.3+
$(document).on(events, selector, data, handler);        // jQuery 1.7+
*/

//used only in "proximity"/"visible" scope, i.e. may be reused without remorse
var tempArg;
var tempSelector;
var tempObject;
var tempEid;
var tempContext;
var tempSelectParent;
var tempURL;
var tempApiUrl;
var temp2;

    
$(document).ready(function() {
    /*
    if (typeof(my_error_log) != "function"){
        // jQuery
        $.getScript(relativePathToBackyardJs + '/' +'debug.js', function() //or the relative path to the calling html is needed??
        {
            // script is now loaded and executed.
            // put your dependent JS here.
        });    
    }
    if (typeof(gi) != "function"){ //@TODO - když skript natáhnu takto, tak gi níže na "tempObject[tempSelector]=gi(tempSelector);" není definováno 
        // jQuery
        $.getScript(relativePathToBackyardJs + '/' + 'basic.js', function() //or the relative path to the calling html is needed??
        {});    
    }
    */

    $('a.coloursave, button.coloursave').bind('click', function(e){//(e){
        //console.log(e.type);//debug        
        if($(this).attr('id') === undefined){
            my_error_log('Error: pls add id to this element.',3);
            alert('Error: pls add id to this element.');
            return false;
        }
        tempSelector=$(this).attr('id');        

        if($('#' + tempSelector).data('coloursave-arg')){
            tempArg = $('#' + tempSelector).data('coloursave-arg');
        } else {
            my_error_log(tempSelector + ' has no argument',2);
            return false;
        }
        console.log(tempArg);        

        tempSelectParent = '#' + tempSelector;
        if($('#' + tempSelector).data('parent')){
            tempSelectParent = '#' + $('#' + tempSelector).data('parent');
            //console.log('slide ' + tempSelectParent + ' parent of ' + tempSelector + ' disabled');
        }

        if($('#' + tempSelector).data('coloursave-context')){
            temp2 = JSON.stringify($('#' + tempSelector).data('coloursave-context'));
            //console.log(temp2);
            if(IsJsonString(temp2)){
                tempContext=temp2;
            } else {
                my_error_log(tempSelector + ' has invalid context. JSON expected. Received:' + temp2,3);
            }
        }
       
        //for eventId analytics
        tempEid=0;
        if($(this).data('eid'))tempEid=$(this).data('eid');

        tempApiUrl = apiUrlColoursave;
        if($('#' + tempSelector).data('coloursave-api')){
            tempApiUrl=$('#' + tempSelector).data('coloursave-api');
        }

        colourSubmitForm(
            {action: 'api_update', api_arg: tempArg, api_context: tempContext, eid: tempEid},   //parameters,
            tempApiUrl, //apiUrl,
            null, //urlDone,
            tempSelectParent, //currentTarget,
            null  //callback
        );
        //není voláno z form//return false;//The return false is blocking the default form submit action.
    });

    $('select.coloursave, input[type="radio"].coloursave').change(function(){
        tempSelector=$(this).attr('name');
        //console.log("TS:" + tempSelector);
        if(tempSelector === undefined){
            alert('Pls add name to this element');
        }
        tempObject = new Object;
        tempObject[tempSelector]=gi(tempSelector);
        tempArg=JSON.stringify(tempObject);
        tempSelectParent = null;
        tempURL = null;
        if(tempSelector == 'owner_language'){
            tempURL = document.location.href;//refresh to change the locale language by server page generation
        }
        if($('[name=' + tempSelector + ']').is('select')){
            if($('[name=' + tempSelector + ']').data('parent')){
                tempSelectParent = '#' + $('[name=' + tempSelector + ']').data('parent');
                //console.log('slide ' + tempSelectParent + ' parent of ' + tempSelector + ' disabled');
            } else {
                my_error_log('For ' + tempSelector + ' parent expected',3);
            }
        }
        if($('[name=' + tempSelector + ']').is('input[type="radio"]')){
            if(typeof $('[name=' + tempSelector + ']').checkboxradio === 'function'){
                $('[name=' + tempSelector + ']').checkboxradio();//http://www.qlambda.com/2012/06/jqm-refresh-jquery-mobile-select-menu.html
                $('[name=' + tempSelector + ']').checkboxradio('disable');                
            } else {
                $('[name=' + tempSelector + ']').attr('disabled',true);//.removeAttr("disabled");
            }
            //console.log('checkbox ' + tempSelector + ' disabled');            
        } else if($('[name=' + tempSelector + ']').is('input')){
            $('[name=' + tempSelector + ']').textinput();//http://www.qlambda.com/2012/06/jqm-refresh-jquery-mobile-select-menu.html
            $('[name=' + tempSelector + ']').textinput('disable');
            //console.log('textinput ' + tempSelector + ' disabled');                        
        }

        if($('[name=' + tempSelector + ']').data('coloursave-context')){
            temp2 = JSON.stringify($('[name=' + tempSelector + ']').data('coloursave-context'));
            //console.log(temp2);
            if(IsJsonString(temp2)){
                tempContext=temp2;
            } else {
                my_error_log(tempSelector + ' has invalid context. JSON expected. Received:' + temp2,3);
            }
        }

        tempApiUrl = apiUrlColoursave;
        if($('[name=' + tempSelector + ']').data('coloursave-api')){
            tempApiUrl=$('[name=' + tempSelector + ']').data('coloursave-api');
        }

        colourSubmitForm(
            {action: 'api_update', api_arg: tempArg},
            tempApiUrl,
            tempURL,
            tempSelectParent,
            function(tmp){
                //console.log(tempSelector + ' ' + tmp + ' callbacked');
                return function() {
                    //console.log("i = " + tmp);
                    if($('[name=' + tmp + ']').is('input[type="radio"]')){
                        if(typeof $('[name=' + tmp + ']').checkboxradio === 'function'){
                            $('[name=' + tmp + ']').checkboxradio('enable');
                        } else {
                            $('[name=' + tmp + ']').removeAttr("disabled");
                        }                        
                    }
                    else if($('[name=' + tempSelector + ']').is('input')){$('[name=' + tempSelector + ']').textinput('enable');}
                };
            }(tempSelector)
        );
        //není voláno z form//return false;//The return false is blocking the default form submit action.
    });    
});//$(document).ready(function() {

$(document).on(
     "focusin", //"keydown focusin", function (e) .. keydown vždy přemaže placeholder
     'textarea.coloursave, input[type="text"].coloursave, input[type="date"].coloursave, input[type="email"].coloursave, input[type="tel"].coloursave',
     function (e) 
{ 
        $(this).css("color", "red");
        if($(this).val() != ""){
            $(this).attr("placeholder", $(this).val());
        }
});
$(document).on(
     "focusout", //keyup píše každé písmeno
     'textarea.coloursave, input[type="text"].coloursave, input[type="date"].coloursave, input[type="email"].coloursave, input[type="tel"].coloursave',
     function (e) 
{ 
    var isFormValid = true;
    if($(this).hasClass("required")){
        if ($.trim($(this).val()).length == 0){
            $(this).parent().addClass("colourSaveHighlight");//@TODO 3 - možná bude potřeba jít přes selector v parents
            if(isFormValid)$(this).focus();//focus na prvni
            isFormValid = false;            
        } else {
            $(this).parent().removeClass("colourSaveHighlight");//@TODO 3 - možná bude potřeba jít přes selector v parents
        }
    };
    if (!isFormValid) {
        alert(localisationString['fill_in_red']);
        return isFormValid;
    }
    
  if($(this).attr("placeholder") != $(this).val()){
    $(this).css("color", "green");
        tempSelector=$(this).attr('name');
        //console.log("TS:" + tempSelector);        
        if(tempSelector === undefined){
            alert('Pls add name to this element');
        }
        tempObject = new Object;
        tempObject[tempSelector]=gi(tempSelector);
        tempArg=JSON.stringify(tempObject);
        //console.log(tempArg);
        
        if($('[name=' + tempSelector + ']').data('coloursave-context')){
            temp2 = JSON.stringify($('[name=' + tempSelector + ']').data('coloursave-context'));
            //console.log(temp2);
            if(IsJsonString(temp2)){
                tempContext=temp2;
            } else {
                my_error_log(tempSelector + ' has invalid context. JSON expected. Received:' + temp2,3);
            }
        }
      /*  //@TODO tempContext dle sumbit api .. lze nějak precall?
        tempObject = new Object;
        if(projectId)tempObject['project_id']=projectId;
        if(personId)tempObject['person_id']=personId;
        if(relationId)tempObject['relation_id']=relationId;
        tempContext=JSON.stringify(tempObject);
        */
       
        //for eventId analytics
        tempEid=0;
        if($(this).data('eid'))tempEid=$(this).data('eid');

        tempApiUrl = apiUrlColoursave;
        if($('[name=' + tempSelector + ']').data('coloursave-api')){
            tempApiUrl=$('[name=' + tempSelector + ']').data('coloursave-api');
        }
        
        //console.log(tempArg);
        colourSubmitForm(
          {action: 'api_update', api_arg: tempArg, api_context: tempContext, eid: tempEid},//@TODO - kam submit?
          tempApiUrl,
          null, //urlDone - no redirect
          null, //currentTarget - not needed for this type of input element
          //function(){$('[name=' + tempSelector + ']').css('color', 'black');}//this was wrong - see below how it is done right
          function(tmp){
              //console.log(tempSelector + ' ' + tmp + ' callbacked');
                return function() {
                    //console.log("i = " + tmp);
                    $('[name=' + tmp + ']').css('color', 'black');
                };
          }(tempSelector)          
        );
   } else {
        $(this).css("color", "black");//black is expected to be default //@TODO načíst do proměnné barvu a vrátit pak původní
   }
});                    

/**
 * needs localisationString['sent_to_server']
 */
/** 
 *  //migration script
 *  function submitStakanForm(parameters,url,currentTarget,callback){
 *      return colourSubmitForm(parameters,'',url,currentTarget,callback){
 *  }
 */
function colourSubmitForm(parameters,apiUrl,urlDone,currentTarget,callback){
    if(apiUrl == null){
        apiUrl = '';//this page
    }
    var descriptionId = '';
    if(currentTarget){
        //console.log('will hide ' + currentTarget);
        descriptionId = currentTarget.substring(1) + '-wait';
        if($('#'+descriptionId).length == 0){//na iPhone někdy zdvojovalo přidanou description
            var newDetail = document.createElement('div');
            newDetail.setAttribute('id', descriptionId);
            var newContent = document.createTextNode(localisationString['sent_to_server']);//debug.. + ' (1) ' + descriptionId + ' ' + tempM1 + ' ' + Math.floor((Math.random()*100)+200));//debug.. + ' ' + dump(parameters) + ' ' + dump(currentTarget));
            newDetail.appendChild(newContent);
            $(currentTarget).before(newDetail);        
            $(currentTarget).fadeOut('slow');//deactivate currentTarget ... to enable .removeAttr('disabled');
        } else {
            my_error_log("Double vclick on " + descriptionId,3, apiUrlErrorLog);           
            return false;
        }
    }
    //debug//console.log(parameters);
    var jqxhr = $.ajax( {
        url: apiUrl,
        type: 'POST',
        data: parameters,
        dataType: 'json'
    } )
    .fail(function(error){
        if(error.responseText){//contains the response
            my_error_log('failed json reply: ' + error.responseText,2, apiUrlErrorLog);
            alert("Error received");//@TODO 2 - vylepšit
        } else {//user probably tries to navigate away before it is finished
            my_error_log('no reply acquired',2, apiUrlErrorLog);
            //@TODO 2 improve this//alert("Continue without server confirmation");//You are so fast.
        }
        if(descriptionId){$('#'+descriptionId).fadeOut('slow',
           function(){$('#'+descriptionId).remove();
           if(currentTarget)$(currentTarget).fadeIn('slow');} 
        )} else if(currentTarget)$(currentTarget).fadeIn('slow');//hide info about server & reactivate currentTarget        
    })    
    .done(function(msg) {
        if(!(msg.error === undefined)){alert(msg.error);}//pak změnit na json a toto vrátit
        if(descriptionId){$('#'+descriptionId).fadeOut('slow',
           function(){$('#'+descriptionId).remove();
           if(currentTarget)$(currentTarget).fadeIn('slow');} 
        )} else if(currentTarget)$(currentTarget).fadeIn('slow');//hide info about server & reactivate currentTarget
        typeof callback == "function" && callback();
        if(urlDone != null){
            window.location.href=urlDone; //http://stackoverflow.com/questions/503093/how-can-i-make-a-redirect-page-in-jquery-javascript
        }}
    );
    return true;
}
//@TODO 4 - submitStakanForm(parameters,url)
// sanitize parameters
// Dat info, ze poslu .. Misto sending buttonu
// Poslat
// Dat info, vyckejte .. Misto sending buttonu
// OnSuccess: redirect; // dat button zpet? Nebo tam priste bude automaticky
