/*!
 * ajaxsave v1 (130829)
 * @TODO 1 - přepsat tak, aby funkce nebyly v konfliktu s původním kódem a refaktorovat ve Stakan1  
 */
'use strict';
//TBD: Use single quote instead of double quotes.

//used only in "proximity"/"visible" scope
var tempArg;
var tempSelector;
var tempObject;
var tempEid;
var tempContext;

$(document).ready(function() {
    // needs localisationString['fill_in_red']
    /*******************************************************************************
     * Validate form fields in Sign up form //http://stackoverflow.com/questions/8402147/jquery-check-inputs-not-empty-before-submit        
     * pro display n.15
     * $(this).find(" a nikoli $(".update-form ... umožňuje aby na jedné stránce (v jiném div) bylo víc formulářů
     */
    $(".update-form").submit(function() {
        var isFormValid = true;
        $(this).find(".required input:text").each(function() { // Note the :text
            if ($.trim($(this).val()).length === 0) {
                $(this).parents('.required').addClass("highlight");
                isFormValid = false;
            } else {
                $(this).parents('.required').removeClass("highlight");
            }
        });
        if (!isFormValid) {alert(localisationString['fill_in_red']); }
        return isFormValid;
    });


    //ad display 3
    //needs           curPageURL(false), $ownerId, $projectId
    $('#addStakeholder').submit(function() {
        var isFormValid = true;
        $(this).find('.required input:text').each(function() { // Note the :text
            if ($.trim($(this).val()).length === 0) {
                $(this).parents('.required').addClass("highlight");
                isFormValid = false;
            } else {
                $(this).parents('.required').removeClass('highlight');
            }
        });
        if (!isFormValid) {alert(localisationString['fill_in_red']); }

        if (isFormValid) {
            if(
                submitStakanForm(
                    {action: 'json_add_stakeholder', eid: 77, project_id: projectId, owner_id: ownerId, person_name: gi('person_name'),
                    relation_task: gi('relation_task'), relation_interest: gi('relation_interest'), 
                    relation_importance: gi('relation_importance'), relation_attitude: gi('relation_attitude'), relation_goal: gi('relation_goal')},
                    curPageURLfalse + '?project_id=' + projectId +'&eid=78') //;
              ) {gi('person_name','');gi('relation_task','');gi('relation_goal','');}//flips remember
        }
        return false;//The return false is blocking the default form submit action.
    });    

    //ad display 19    //needs projectId, relationId, personId, ownerId, inputAction
    $('#editRelation').submit(function(){
        submitStakanForm(
          {action: 'json_edit_relation', eid: 74, project_id: projectId, owner_id: ownerId, relation_id: relationId,
            relation_task: gi('relation_task'), relation_next_contact_date: gi('relation_next_contact_date'), relation_interest: gi('relation_interest'), 
            relation_importance: gi('relation_importance'), relation_attitude: gi('relation_attitude'), relation_goal: gi('relation_goal')},
          ((inputAction=="backtoperson")?('?person_id='+personId+'&eid=76'):('?project_id='+projectId+'&eid=75'))
        );
        return false;//The return false is blocking the default form submit action.
    });

    $('#send_me_history_button').bind('vclick', function(){
        //alert(dump(e));
        submitStakanForm(
          {action: 'send_project_history_json', eid: 90, project_id: projectId},
          null,
          '#send_me_history_button'
        );
        //není voláno z form//return false;//The return false is blocking the default form submit action.
    });

    $('#send_me_mm_button').bind('vclick', function(){//(e){
        //console.log(e.type);//debug
        submitStakanForm(
          {action: 'send_project_mm_json', eid: 91, project_id: projectId},
          null,
          '#send_me_mm_button'
        );
    });

    $('#select-choice-notify-me-as').change(function(){
        //alert(gi('select-choice-notify-me-as'));
        tempArg = JSON.stringify({ "notify-me-as": gi('select-choice-notify-me-as')});
        //console.log(tempArg);
        submitStakanForm(
          {action: 'api_update', api_arg: tempArg},
          null,
          '#select-choice-notify-me-as-parent'
        );
    });
    
    $('select.ajaxsave, input[type="radio"].ajaxsave').change(function(){
        tempSelector=$(this).attr('name');
        tempObject = new Object;
        tempObject[tempSelector]=gi(tempSelector);
        tempArg=JSON.stringify(tempObject);
        tempSelectParent = null;
        tempURL = null;
        if(tempSelector == 'owner_language'){
            tempURL = curPageURLtrue;
        }
        if($('[name=' + tempSelector + ']').is('select')){
            if($('[name=' + tempSelector + ']').data('parent')){
                tempSelectParent = '#' + $('[name=' + tempSelector + ']').data('parent');
                //console.log('slide ' + tempSelectParent + ' parent of ' + tempSelector + ' disabled');
            }
        }
        if($('[name=' + tempSelector + ']').is('input[type="radio"]')){
            $('[name=' + tempSelector + ']').checkboxradio();//http://www.qlambda.com/2012/06/jqm-refresh-jquery-mobile-select-menu.html
            $('[name=' + tempSelector + ']').checkboxradio('disable');
            //console.log('checkbox ' + tempSelector + ' disabled');            
        } else if($('[name=' + tempSelector + ']').is('input')){
            $('[name=' + tempSelector + ']').textinput();//http://www.qlambda.com/2012/06/jqm-refresh-jquery-mobile-select-menu.html
            $('[name=' + tempSelector + ']').textinput('disable');
            //console.log('textinput ' + tempSelector + ' disabled');                        
        }
        submitStakanForm(
            {action: 'api_update', api_arg: tempArg},
            tempURL, tempSelectParent,
            function(tmp){
                //console.log(tempSelector + ' ' + tmp + ' callbacked');
                return function() {
                    //console.log("i = " + tmp);
                    if($('[name=' + tmp + ']').is('input[type="radio"]')){$('[name=' + tmp + ']').checkboxradio('enable');}
                    else if($('[name=' + tempSelector + ']').is('input')){$('[name=' + tempSelector + ']').textinput('enable');}
                };
            }(tempSelector)
        );
        //není voláno z form//return false;//The return false is blocking the default form submit action.
    });    
});//$(document).ready(function() {

$(document).on(
     "focusin", 'textarea.ajaxsave, input[type="text"].ajaxsave, input[type="date"].ajaxsave, input[type="email"].ajaxsave, input[type="tel"].ajaxsave',function (e) //"keydown focusin", function (e) .. keydown vždy přemaže placeholder
{ 
        $(this).css("color", "red");
        if($(this).val() != ""){
            $(this).attr("placeholder", $(this).val());
        }
});
$(document).on(
     "focusout", 'textarea.ajaxsave, input[type="text"].ajaxsave, input[type="date"].ajaxsave, input[type="email"].ajaxsave, input[type="tel"].ajaxsave', function (e) //keyup píše každé písmeno
{ 
  if($(this).attr("placeholder") != $(this).val()){
    $(this).css("color", "green");
        tempSelector=$(this).attr('name');    
        tempObject = new Object;
        tempObject[tempSelector]=gi(tempSelector);
        tempArg=JSON.stringify(tempObject);
        tempObject = new Object;
        if(projectId)tempObject['project_id']=projectId;
        if(personId)tempObject['person_id']=personId;
        if(relationId)tempObject['relation_id']=relationId;
        tempContext=JSON.stringify(tempObject);
        tempEid=0;
        if($(this).data('eid'))tempEid=$(this).data('eid');
        //console.log(tempArg);
        submitStakanForm(
          {action: 'api_update', api_arg: tempArg, api_context: tempContext, eid: tempEid},
          null,null
          //,function(){$('[name=' + tempSelector + ']').css('color', 'black');}//this was wrong - see below how it is done right
          ,function(tmp){
              //console.log(tempSelector + ' ' + tmp + ' callbacked');
                return function() {
                    //console.log("i = " + tmp);
                    $('[name=' + tmp + ']').css('color', 'black');
                };
          }(tempSelector)          
        );
   } else {
        $(this).css("color", "black");//black is expected to be default
   }
});                    

$(document).on(
     "focusin", '#meetingMinutesForm input[type="text"],#meetingMinutesForm textarea, #update-form input[type="text"], #update-form textarea',function (e) //"keydown focusin", function (e) .. keydown vždy přemaže placeholder
{
    $(this).css("color", "red");
    if($(this).val() != ""){
        $(this).attr("placeholder", $(this).val());
    }
});

$(document).on(
     "focusout", '#meetingMinutesForm input[type="text"],#meetingMinutesForm textarea', function (e) //keyup píše každé písmeno
{ 
    if($(this).attr("placeholder") != $(this).val()){
        $(this).css("color", "green");
        var jqxhr = $.ajax({
            url: "",//this page
            type: "POST",
            data: {action: "json_edit_relations_mm", eid: 83, relation_id: $(this).attr("rel"), relation_task: $(this).val()},
            dataType: "json"
        })
        .done(function(data) {
                $("#relation_task-" + data.relation_id).css("color", "black");//black is expected to be default
            }
        );
    } else {
        $(this).css("color", "black");//black is expected to be default
    }
});                    

$(document).on(
     "focusout", '#update-form input[type="text"], #update-form textarea', function (e) //keyup píše každé písmeno
{ 
    var isFormValid = true;
    if($(this).hasClass("required")){
        if ($.trim($(this).val()).length == 0){
            $(this).parent().addClass("highlight");//@TODO 3 - možná bude potřeba jít přes selector v parents
            if(isFormValid)$(this).focus();//focus na prvni
            isFormValid = false;            
        } else {
            $(this).parent().removeClass("highlight");//@TODO 3 - možná bude potřeba jít přes selector v parents
        }
    };
    if (!isFormValid) {
        alert(localisationString['fill_in_red']);
        return isFormValid;
    }
    
    if($(this).attr("placeholder") != $(this).val()){
        $(this).css("color", "green");
    
        if(this.id == 'project_status'){//eid 88
            var jqxhr = $.ajax( {
                url: "",//this page
                type: "POST",
                data: {action: "edit_project_status_json", eid: 88, project_status: $(this).val()},
                dataType: "json"
            })
            .done(function(data) {
                    $("#" + data.input_id).css("color", "black");//black is expected to be default
                }
            );
        } else if(this.id == 'project_name'){//eid 87
            var jqxhr = $.ajax( {
                url: "",//this page
                type: "POST",
                data: {action: "edit_project_name_json", eid: 87, project_name: $(this).val()},
                dataType: "json"
            } )
            .done(function(data) {
                    $("#" + data.input_id).css("color", "black");//black is expected to be default
                    //zde by mohlo být rename stránky, ale pozor na JS injecting
                }
            );

        } else {
            my_error_log('Neprovede eid=87',2);
            alert('Error');
        }        
   } else {
        $(this).css("color", "black");//black is expected to be default
   }
});                    

/**
 * needs localisationString['sent_to_server']
 */
function submitStakanForm(parameters,url,currentTarget,callback){
  var apiURL = '';//this page
    var descriptionId = '';
    if(currentTarget){
        descriptionId = currentTarget.substring(1) + '-wait';
        if($('#'+descriptionId).length == 0){//na iPhone někdy zdvojovalo přidanou description
            var newDetail = document.createElement('div');
            newDetail.setAttribute('id', descriptionId);
            var newContent = document.createTextNode(localisationString['sent_to_server']);//debug.. + ' (1) ' + descriptionId + ' ' + tempM1 + ' ' + Math.floor((Math.random()*100)+200));//debug.. + ' ' + dump(parameters) + ' ' + dump(currentTarget));
            newDetail.appendChild(newContent);
            $(currentTarget).before(newDetail);        
            $(currentTarget).fadeOut('slow');//deactivate currentTarget ... to enable .removeAttr('disabled');
        } else {
            my_error_log("Double vclick on " + descriptionId,3);           
            return false;
        }
    }
    var jqxhr = $.ajax( {
        url: apiURL,
        type: 'POST', //130226 když by tu bylo post, tak nefunguje přechod na URL
        data: parameters,
        dataType: 'json'//130226 měním text na json, snad to není issue
    } )
    .fail(function(error){
        if(error.responseText){//contains the response
            my_error_log('failed json reply: ' + error.responseText,2);
            alert("Error received");//@TODO 2 - vylepšit
        } else {//user probably tries to navigate away before it is finished
            my_error_log('no reply acquired',2);
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
        if(url != null){
            window.location.href=url; //http://stackoverflow.com/questions/503093/how-can-i-make-a-redirect-page-in-jquery-javascript
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


/* NOTE:
$(selector).live(events, data, handler);                // jQuery 1.3+
$(document).delegate(selector, events, data, handler);  // jQuery 1.4.3+
$(document).on(events, selector, data, handler);        // jQuery 1.7+
*/
