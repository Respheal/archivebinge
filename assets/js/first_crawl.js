var ajaxRequestIntervalMs = 1000;
var ajaxRequestUrl = './a.json';
var loaderImageUrl = './ajax-loader.gif';
var loaderDivName = 'loaderDiv';
 
// http://www.w3schools.com/js/js_obj_boolean.asp
var isLoading=new Boolean(); 
isLoading=false;
 
//JavaScript's built-in setInterval() function
setInterval(
    function(){                      
        $.ajax({
            url: ajaxRequestUrl,
            type: "GET",
            cache: false,                                  
            statusCode: {
                // HTTP-Code "Page not found"
                404: function() {
                    if (isLoading===false){
                        showLoader();
                    }
                },
                // HTTP-Code "Success"
                200: function() {
                    if (isLoading===true){
                        $.getJSON(ajaxRequestUrl, function(json) {
			    var str = JSON.stringify(json, null, 2);
		            //console.log(json);
                            hideLoader(str);
                        });
                    }
                }    
            }
        });     
    },
    ajaxRequestIntervalMs
);
 
// ------------ show- and hide-functions for the overlay -----------------
function showLoader(){
    $("#" + loaderDivName).remove();
    $("body").append("<div id='" + loaderDivName + "'><img src='"+loaderImageUrl+"' /></div>");
    isLoading=true;
};
  
function hideLoader(str){
    $("#" + loaderDivName).remove();
    $("body").append("<div id='" + loaderDivName + "'>"+str+"</div>");
    //console.log(str);
    isLoading=false;
};
