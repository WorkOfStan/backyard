"use strict";
/* global $ */
//backyard 1
/*!
 * js for stakan
 * version: 1 (2013-05-03)
 * version: 2 (2013-05-08), apiContext, toggleStakeholderContacts()
 * version 3 (2013-05-26), fix proti na iPhone někdy zdvojovalo přidanou description
 * v.4 (2013-05-28), ajaxsave i pro select, zakomentovány console.log, aby fungovalo na Lumia 800
 */
//TBD: Use single quote instead of double quotes.
//specific

//used only in "proximity"/"visible" scope
var tempArg;
var tempSelector;
var tempObject;
var tempEid;
var tempContext;

$(document).ready(function () {
  // needs localisationString['fill_in_red']
  /*******************************************************************************
   * Validate form fields in Sign up form //http://stackoverflow.com/questions/8402147/jquery-check-inputs-not-empty-before-submit
   * pro display n.15
   * $(this).find(" a nikoli $(".update-form ... umožňuje aby na jedné stránce (v jiném div) bylo víc formulářů
   */
  $(".update-form").submit(function () {
    var isFormValid = true;
    $(this)
      .find(".required input:text")
      .each(function () {
        // Note the :text
        if ($.trim($(this).val()).length === 0) {
          $(this).parents(".required").addClass("highlight");
          isFormValid = false;
        } else {
          $(this).parents(".required").removeClass("highlight");
        }
      });
    if (!isFormValid) {
      alert(localisationString["fill_in_red"]);
    }
    return isFormValid;
  });

  //ad display 3
  //needs           curPageURL(false), $ownerId, $projectId
  $("#addStakeholder").submit(function () {
    var isFormValid = true;
    $(this)
      .find(".required input:text")
      .each(function () {
        // Note the :text
        if ($.trim($(this).val()).length === 0) {
          //$(this).parent().addClass('highlight');
          $(this).parents(".required").addClass("highlight");
          isFormValid = false;
        } else {
          //$(this).parent().removeClass('highlight');
          $(this).parents(".required").removeClass("highlight");
        }
      });
    if (!isFormValid) {
      alert(localisationString["fill_in_red"]);
    }

    if (isFormValid) {
      if (
        submitStakanForm(
          {
            action: "json_add_stakeholder",
            eid: 77,
            project_id: projectId,
            owner_id: ownerId,
            person_name: gi("person_name"),
            relation_task: gi("relation_task"),
            relation_interest: gi("relation_interest"),
            relation_importance: gi("relation_importance"),
            relation_attitude: gi("relation_attitude"),
            relation_goal: gi("relation_goal"),
          },
          curPageURLfalse + "?project_id=" + projectId + "&eid=78",
        ) //;
      ) {
        gi("person_name", "");
        gi("relation_task", "");
        gi("relation_goal", "");
      } //flips remember
    }
    return false; //The return false is blocking the default form submit action.
  });

  //ad display 19
  //needs projectId, relationId, personId, ownerId, inputAction
  $("#editRelation").submit(function () {
    submitStakanForm(
      {
        action: "json_edit_relation",
        eid: 74,
        project_id: projectId,
        owner_id: ownerId,
        relation_id: relationId,
        relation_task: gi("relation_task"),
        relation_next_contact_date: gi("relation_next_contact_date"),
        relation_interest: gi("relation_interest"),
        relation_importance: gi("relation_importance"),
        relation_attitude: gi("relation_attitude"),
        relation_goal: gi("relation_goal"),
      },
      inputAction === "backtoperson"
        ? "?person_id=" + personId + "&eid=76"
        : "?project_id=" + projectId + "&eid=75",
    );
    return false; //The return false is blocking the default form submit action.
  });

  $("#send_me_history_button").bind("vclick", function () {
    //alert(dump(e));
    submitStakanForm(
      { action: "send_project_history_json", eid: 90, project_id: projectId },
      null,
      "#send_me_history_button",
    );
    //není voláno z form//return false;//The return false is blocking the default form submit action.
  });

  $("#send-me-mm-button").bind("vclick", function () {
    //(e){
    //console.log(e.type);//debug
    submitStakanForm(
      { action: "send_project_mm_json", eid: 91, project_id: projectId },
      null,
      "#send-me-mm-button",
    );
    //není voláno z form//return false;//The return false is blocking the default form submit action.
  });

  $("#select-choice-notify-me-as").change(function () {
    //alert(gi('select-choice-notify-me-as'));
    tempArg = JSON.stringify({
      "notify-me-as": gi("select-choice-notify-me-as"),
    });
    //console.log(tempArg);
    submitStakanForm(
      { action: "api_update", api_arg: tempArg },
      null,
      "#select-choice-notify-me-as-parent",
    );
    //není voláno z form//return false;//The return false is blocking the default form submit action.
  });

  $('select.ajaxsave, input[type="radio"].ajaxsave').change(function () {
    tempSelector = $(this).attr("name");
    tempObject = new Object();
    tempObject[tempSelector] = gi(tempSelector);
    tempArg = JSON.stringify(tempObject);
    //console.log(tempArg);
    tempSelectParent = null;
    tempURL = null;
    if (tempSelector === "owner-language") {
      tempURL = curPageURLtrue;
    }
    if ($("[name=" + tempSelector + "]").is("select")) {
      if ($("[name=" + tempSelector + "]").data("parent")) {
        tempSelectParent =
          "#" + $("[name=" + tempSelector + "]").data("parent");
        //console.log('slide ' + tempSelectParent + ' parent of ' + tempSelector + ' disabled');
      }
    }
    if ($("[name=" + tempSelector + "]").is('input[type="radio"]')) {
      $("[name=" + tempSelector + "]").checkboxradio(); //http://www.qlambda.com/2012/06/jqm-refresh-jquery-mobile-select-menu.html
      $("[name=" + tempSelector + "]").checkboxradio("disable");
      //console.log('checkbox ' + tempSelector + ' disabled');
    } else if ($("[name=" + tempSelector + "]").is("input")) {
      $("[name=" + tempSelector + "]").textinput(); //http://www.qlambda.com/2012/06/jqm-refresh-jquery-mobile-select-menu.html
      $("[name=" + tempSelector + "]").textinput("disable");
      //console.log('textinput ' + tempSelector + ' disabled');
    }
    submitStakanForm(
      { action: "api_update", api_arg: tempArg },
      tempURL,
      tempSelectParent,
      (function (tmp) {
        //console.log(tempSelector + ' ' + tmp + ' callbacked');
        return function () {
          //console.log("i = " + tmp);
          if ($("[name=" + tmp + "]").is('input[type="radio"]')) {
            $("[name=" + tmp + "]").checkboxradio("enable");
          } else if ($("[name=" + tempSelector + "]").is("input")) {
            $("[name=" + tempSelector + "]").textinput("enable");
          }
        };
      })(tempSelector),
    );
    //není voláno z form//return false;//The return false is blocking the default form submit action.
  });

  //jako poslední v document.ready:
  $("#tweets").tweets({
    tweets: 2,
    username: "s_rejthar",
  });
}); //$(document).ready(function() {

$(document).on(
  //$('form').on(
  "focusin",
  'textarea.ajaxsave, input[type="text"].ajaxsave, input[type="date"].ajaxsave, input[type="email"].ajaxsave, input[type="tel"].ajaxsave',
  function (e) {
    //"keydown focusin", function (e) .. keydown vždy přemaže placeholder
    //alert($(this).attr('name'));//debug
    $(this).css("color", "red");
    if ($(this).val() !== "") {
      $(this).attr("placeholder", $(this).val());
    }
  },
);
$(document).on(
  "focusout",
  'textarea.ajaxsave, input[type="text"].ajaxsave, input[type="date"].ajaxsave, input[type="email"].ajaxsave, input[type="tel"].ajaxsave',
  function (e) {
    //keyup píše každé písmeno
    if ($(this).attr("placeholder") !== $(this).val()) {
      $(this).css("color", "green");
      tempSelector = $(this).attr("name");
      tempObject = new Object();
      tempObject[tempSelector] = gi(tempSelector);
      tempArg = JSON.stringify(tempObject);
      tempObject = new Object();
      if (projectId) tempObject["project_id"] = projectId;
      if (personId) tempObject["person_id"] = personId;
      if (relationId) tempObject["relation_id"] = relationId;
      tempContext = JSON.stringify(tempObject);
      tempEid = 0;
      if ($(this).data("eid")) {
        tempEid = $(this).data("eid");
      }
      //console.log(tempArg);
      submitStakanForm(
        {
          action: "api_update",
          api_arg: tempArg,
          api_context: tempContext,
          eid: tempEid,
        },
        null,
        null,
        //,function(){$('[name=' + tempSelector + ']').css('color', 'black');}//@TODO 1 - see below
        (function (tmp) {
          //console.log(tempSelector + ' ' + tmp + ' callbacked');
          return function () {
            //console.log("i = " + tmp);
            $("[name=" + tmp + "]").css("color", "black");
          };
        })(tempSelector),
      );
    } else {
      $(this).css("color", "black"); //black is expected to be default
    }
  },
);

//$('#meetingMinutesForm input[type="text"],#meetingMinutesForm textarea, #update-form input[type="text"], #update-form textarea').on(//till jQ 1.9: .live(
$(document).on(
  //till jQ 1.9: .live(
  //$('.ui-content').on(
  "focusin",
  '#meetingMinutesForm input[type="text"],#meetingMinutesForm textarea, #update-form input[type="text"], #update-form textarea',
  function (e) {
    //"keydown focusin", function (e) .. keydown vždy přemaže placeholder
    $(this).css("color", "red");
    if ($(this).val() !== "") {
      $(this).attr("placeholder", $(this).val());
    }
  },
);
//$('#meetingMinutesForm input[type="text"],#meetingMinutesForm textarea').on(//till jQ 1.9: .live(
$(document).on(
  //till jQ 1.9: .live(
  //$("#meetingMinutesForm").on(
  "focusout",
  '#meetingMinutesForm input[type="text"],#meetingMinutesForm textarea',
  function (e) {
    //keyup píše každé písmeno
    //"focusout", 'input[type="text"],textarea', function (e) //keyup píše každé písmeno
    if ($(this).attr("placeholder") !== $(this).val()) {
      $(this).css("color", "green");
      var jqxhr = $.ajax({
        url: "", //this page
        type: "POST",
        data: {
          action: "json_edit_relations_mm",
          eid: 83,
          relation_id: $(this).attr("rel"),
          relation_task: $(this).val(),
        },
        dataType: "json", //,
        //success: function(data) { //@TODO 2 .. přepsat do .done
        //console.log(data);
        //    $("#relation_task-" + data.relation_id).css("color", "black");//black is expected to be default
        //}
      }).done(function (data) {
        //console.log(data);
        $("#relation_task-" + data.relation_id).css("color", "black"); //black is expected to be default
      });
    } else {
      $(this).css("color", "black"); //black is expected to be default
    }
  },
);

//$('#update-form input[type="text"], #update-form textarea').on(//till jQ 1.9: .live(
$(document).on(
  //till jQ 1.9: .live(
  //$('.ui-content').on(
  "focusout",
  '#update-form input[type="text"], #update-form textarea',
  function (e) {
    //keyup píše každé písmeno
    var isFormValid = true;
    //$(this).find(".required input:text").each(function(){ // Note the :text
    //alert($(this).hasClass("required"));
    //$(this).hasClass("required").each(function(){ // @TODO - Note, že není - the :text - nebude to blbnout u textarea?
    //    alert('testuji required');
    //});
    //$(this).find(".required input:text").each(function(){ // Note the :text
    if ($(this).hasClass("required")) {
      if ($.trim($(this).val()).length === 0) {
        $(this).parent().addClass("highlight"); //@TODO 3 - možná bude potřeba jít přes selector v parents
        if (isFormValid) $(this).focus(); //focus na prvni
        isFormValid = false;
      } else {
        $(this).parent().removeClass("highlight"); //@TODO 3 - možná bude potřeba jít přes selector v parents
      }
    }
    //if (!isFormValid) alert("'.localisationString('fill_in_red').'");
    if (!isFormValid) {
      alert(localisationString["fill_in_red"]);
      return isFormValid;
    }

    if ($(this).attr("placeholder") !== $(this).val()) {
      $(this).css("color", "green");

      if (this.id === "project_status") {
        //eid 88
        //alert($(this).val());return false;
        var jqxhr = $.ajax({
          url: "", //this page
          type: "POST",
          data: {
            action: "edit_project_status_json",
            eid: 88,
            project_status: $(this).val(),
          },
          dataType: "json", //,
          //success: function(data) { //@TODO 2 .. přepsat do .done
          //console.log(data);
          //    $("#" + data.input_id).css("color", "black");//black is expected to be default
          //}
        }).done(function (data) {
          //console.log(data);
          $("#" + data.input_id).css("color", "black"); //black is expected to be default
        });
      } else if (this.id === "project_name") {
        //eid 87
        var jqxhr = $.ajax({
          url: "", //this page
          type: "POST",
          data: {
            action: "edit_project_name_json",
            eid: 87,
            project_name: $(this).val(),
          },
          dataType: "json", //,
          //success: function(data) { //@TODO 2 .. přepsat do .done
          //console.log(data);
          //    $("#" + data.input_id).css("color", "black");//black is expected to be default
          //zde by mohlo být rename stránky, ale pozor na JS injecting
          //}
        }).done(function (data) {
          //console.log(data);
          $("#" + data.input_id).css("color", "black"); //black is expected to be default
          //zde by mohlo být rename stránky, ale pozor na JS injecting
        });
      } else {
        my_error_log("Neprovede eid=87", 2);
        alert("Error");
      }
    } else {
      $(this).css("color", "black"); //black is expected to be default
    }
  },
);

/**
 * needs localisationString['sent_to_server']
 *
 * @param {type} parameters
 * @param {type} url
 * @param {type} currentTarget
 * @param {type} callback
 * @returns {Boolean}
 */
function submitStakanForm(parameters, url, currentTarget, callback) {
  //debug//alert(dump(parameters));//debug
  //debug//alert(url);//debug
  //var tempM1 = Math.floor((Math.random()*100)+1);//debug
  var descriptionId = "";
  if (currentTarget) {
    descriptionId = currentTarget.substring(1) + "-wait";
    if ($("#" + descriptionId).length === 0) {
      //na iPhone někdy zdvojovalo přidanou description
      var newDetail = document.createElement("div");
      newDetail.setAttribute("id", descriptionId);
      var newContent = document.createTextNode(
        localisationString["sent_to_server"],
      ); //debug.. + ' (1) ' + descriptionId + ' ' + tempM1 + ' ' + Math.floor((Math.random()*100)+200));//debug.. + ' ' + dump(parameters) + ' ' + dump(currentTarget));
      newDetail.appendChild(newContent);
      $(currentTarget).before(newDetail);
      $(currentTarget).fadeOut("slow"); //deactivate currentTarget ... to enable .removeAttr('disabled');
    } else {
      my_error_log("Double vclick on " + descriptionId, 3);
      return false;
    }
  }
  var jqxhr = $.ajax({
    url: "", //this page
    type: "POST", //130226 když by tu bylo post, tak nefunguje přechod na URL
    data: parameters,
    dataType: "json", //130226 měním text na json, snad to není issue
  })
    .fail(function (error) {
      if (error.responseText) {
        //contains the response
        my_error_log("failed json reply: " + error.responseText, 2);
        alert("Error received"); //@TODO 2 - vylepšit
      } else {
        //user probably tries to navigate away before it is finished
        my_error_log("no reply acquired", 2);
        //@TODO 2 improve this//alert("Continue without server confirmation");//You are so fast.
      }
      if (descriptionId) {
        $("#" + descriptionId).fadeOut("slow", function () {
          $("#" + descriptionId).remove();
          if (currentTarget) $(currentTarget).fadeIn("slow");
        });
      } else if (currentTarget) $(currentTarget).fadeIn("slow"); //hide info about server & reactivate currentTarget
    })
    .done(function (msg) {
      if (!(msg.error === undefined)) {
        alert(msg.error);
      } //pak změnit na json a toto vrátit
      if (descriptionId) {
        $("#" + descriptionId).fadeOut("slow", function () {
          $("#" + descriptionId).remove();
          if (currentTarget) $(currentTarget).fadeIn("slow");
        });
      } else if (currentTarget) $(currentTarget).fadeIn("slow"); //hide info about server & reactivate currentTarget
      typeof callback === "function" && callback();
      if (url !== null) {
        window.location.href = url; //http://stackoverflow.com/questions/503093/how-can-i-make-a-redirect-page-in-jquery-javascript
      }
    });
  return true;
}
//@TODO 4 - submitStakanForm(parameters,url)
// sanitize parameters
// Dat info, ze poslu .. Misto sending buttonu
// Poslat
// Dat info, vyckejte .. Misto sending buttonu
// OnSuccess: redirect; // dat button zpet? Nebo tam priste bude automaticky

//needs projectId
function informAboutEid(eid) {
  //@TODO 3 - při offline neposílat, ale skladovat k pozdějšímu poslání
  var jqxhr = $.ajax({
    url: "", //this page
    type: "GET",
    //data: {action: "json_only", eid: eid'.(($projectId)?(", project_id: {$projectId}"):('')).'},
    data: { action: "json_only", eid: eid, project_id: projectId }, //@TODO - otestovat co projectId==false
    dataType: "text",
  }); //@TODO 2 - přidat info o error auth.
  return true;
}

/* NOTE:
 $(selector).live(events, data, handler);                // jQuery 1.3+
 $(document).delegate(selector, events, data, handler);  // jQuery 1.4.3+
 $(document).on(events, selector, data, handler);        // jQuery 1.7+
 */

//ad case 3
$(document).one("pageshow", "#stakeholdermap", function () {
  //one brání opakovanému attach
  $("#quadrant1").click(function () {
    $.mobile.changePage("#addstakeholder"); //w/o is faster , { transition: 'flow'} );
    $("#relation_importance").val("1").slider("refresh");
    $("#relation_interest").val("1").slider("refresh");
    $("#hintAddStakeholderButton").hide();
    informAboutEid(32);
  });
  $("#quadrant2").click(function () {
    $.mobile.changePage("#addstakeholder"); //, { transition: 'slideup'} );
    $("#relation_importance").val("1").slider("refresh");
    $("#relation_interest").val("-1").slider("refresh");
    $("#hintAddStakeholderButton").hide();
    informAboutEid(33);
  });
  $("#quadrant3").click(function () {
    $.mobile.changePage("#addstakeholder"); //, { transition: 'slideup'} );
    $("#relation_importance").val("-1").slider("refresh");
    $("#relation_interest").val("1").slider("refresh");
    $("#hintAddStakeholderButton").hide();
    informAboutEid(34);
  });
  $("#quadrant4").click(function () {
    $.mobile.changePage("#addstakeholder"); //, { transition: 'slideup'} );
    $("#relation_importance").val("-1").slider("refresh");
    $("#relation_interest").val("-1").slider("refresh");
    $("#hintAddStakeholderButton").hide();
    informAboutEid(31);
  });
  $("#quadrant1 a, #quadrant2 a, #quadrant3 a, #quadrant4 a").click(
    function (e) {
      e
        .stopPropagation
        //nefunguje//function(){alert('cb after stopprop');$.stayInWebApp('a.stay');}//callback
        (); //http://stackoverflow.com/questions/3864102/how-to-ignore-click-event-when-clicked-on-children //disabluje i $.stayInWebApp('');
      //nefunguje//$.stayInWebApp('a.stay');
    },
  );

  //$('.drag').mobiledraganddrop({ targets: '.drop' , status: '#status' });
});

//created in accordance to jquery.stayInWebApp_s.js pro specifické případy //@TODO 2 - popsat ty specifické případy
function stayInWebAppNow(selector) {
  //detect iOS full screen mode
  if ("standalone" in window.navigator && window.navigator.standalone) {
    //if the selector is empty, default to all links
    if (!selector) {
      //selector = 'a';
      return false;
    }

    //only stay in web app for links that are set to _self (or not set)
    if (
      $(selector).attr("target") === undefined ||
      $(selector).attr("target") === "" ||
      $(selector).attr("target") === "_self"
    ) {
      //get the destination of the link clicked
      var dest = $(selector).attr("href");

      //if the destination is an absolute url, ignore it
      //if(!dest.match(/^http(s?)/g)) {
      if (true) {
        //for all links
        //prevent default behavior (opening safari)
        my_error_log(
          "stayInWebApp for selector=" + selector + " dest=" + dest,
          4,
        );
        event.preventDefault();
        //update location of the web app
        //alert('mydest:' + dest)
        self.location = dest;
      }
    }
    return false;
  }
}

//generic

//for timezone
function timeZoneUpdate() {
  var tz = jstz.determine();
  if (typeof tz === "undefined") {
    my_error_log("No timezone found", 2);
  } else {
    $.ajax({
      type: "GET",
      url: "timezone.php",
      data: "time=" + tz.name(),
    }).fail(function (error) {
      my_error_log("timezone.php call failed: " + error.responseText, 2);
    });
  }
}

function gi(inputname, newvalue) {
  //getInputValueByName
  if (newvalue === undefined) {
    //read
    if ($("[name=" + inputname + "]").attr("type") !== "radio") {
      var result = $("[name=" + inputname + "]").val();
    } else {
      var result = $("[name=" + inputname + "]:checked").val();
    }
    return result;
  } else {
    //write
    if ($("[name=" + inputname + "]").attr("type") !== "radio") {
      var result = $("[name=" + inputname + "]").val(newvalue);
    } else {
      var result = $("[name=" + inputname + "]:checked").val(newvalue); //for radio newvalue must be ['radio1'] or ['radio1', 'radio2'] ; see http://api.jquery.com/val/
    }
    return true;
  }
}

/**
 * Function : dump()
 * Arguments: The data - array,hash(associative array),object
 *    The level - OPTIONAL
 * Returns  : The textual representation of the array.
 * This function was inspired by the print_r function of PHP.
 * This will accept some data as the argument and return a
 * text that will be a more readable version of the
 * array/hash/object that is given.
 * Source: http://binnyva.blogspot.com/2005/10/dump-function-javascript-equivalent-of.html
 *
 * @param {type} arr
 * @param {type} level
 * @returns {String|window.dump.dumped_text}
 */
function dump(arr, level) {
  var dumped_text = "";
  if (!level) {
    level = 0;
  }

  //The padding given at the beginning of the line.
  var level_padding = "";
  for (var j = 0; j < level + 1; j++) level_padding += "    ";

  if (typeof arr === "object") {
    //Array/Hashes/Objects
    for (var item in arr) {
      var value = arr[item];

      if (typeof value === "object") {
        //If it is an array,
        dumped_text += level_padding + "'" + item + "' ...\n";
        dumped_text += dump(value, level + 1);
      } else {
        dumped_text += level_padding + "'" + item + "' => \"" + value + '"\n';
      }
    }
  } else {
    //Stings/Chars/Numbers etc.
    dumped_text = "===>" + arr + "<===(" + typeof arr + ")";
  }
  return dumped_text;
}

/**
 * Requires jQuery
 * Requires include_once (__ROOT__."/lib/my_error_log_js.php");
 *
 * @param {type} message
 * @param {type} level
 * @returns {Boolean}
 */
function my_error_log(message, level) {
  var nameOfThisApp = "stakan1"; //@TODO 4 - adapt na volající skript. Anebo nechat univerzální a adaptovat při každém konkrétním volání? Možná zbytečné, protože v logu je stejně uvedeno jméno volaného skriptu, tedy to, co je v parametru url
  var jqxhr = $.ajax({
    url: "index.php",
    type: "POST",
    data: {
      my_error_log_message: nameOfThisApp + " " + message,
      my_error_log_level: level,
    },
    dataType: "json",
  }); //@TODO 2 - přidat info o error auth.
  return true;
}

function toggleStakeholderContacts() {
  $("#edit_stakeholder_contacts").show();
  $("#stakeholder_button").hide();
  informAboutEid(99);
}
