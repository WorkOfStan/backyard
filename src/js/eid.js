'use strict';
//backyard 1
/*!
 * inform about eid v1 (130829)
 */

//needs projectId
function informAboutEid(eid) {
    var apiURL = '';//this page
    //@TODO 3 - při offline neposílat, ale skladovat k pozdějšímu poslání
    var jqxhr = $.ajax({
        url: apiURL, //this page
        type: 'GET',
        //data: {action: "json_only", eid: eid'.(($projectId)?(", project_id: {$projectId}"):('')).'},
        data: {action: "json_only", eid: eid, project_id: projectId}, //@TODO - otestovat co projectId==false
        dataType: 'text'
    }); //@TODO 2 - přidat info o error auth.
    return true;
}
