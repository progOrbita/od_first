function addInfo(type, value){
    $('#alerts').remove();
    $('#content').prepend(`<div id="alerts">
    <p class="alert alert-${type}">${value}
    <button type="button" class="close" data-dismiss="alert">x</button>
    </p></div>`);
}
function callApi(ajaxAction,ajaxString){
    return $.ajax({
        url: admin_od,
        data: {
            ajax: true,
            action: ajaxAction,
            dataString : ajaxString,
        },
    });
}
function changeToSuccess(value){
    $(value).removeClass("bg-error");
    $(value).addClass("bg-fine");
}
function changeToError(value){
    $(value).removeClass("bg-fine");
    $(value).addClass("bg-error");
}

$(document).ready(function(){

    $(document).on('click','#btnSubmit',function(){
        let name = $('#form_name').val();
        let age = $('#form_age').val();
        let date = $('#form_date').val();
        let saveArray = [name,age,date];
        let saveJson = JSON.stringify(saveArray);
        
        let ajaxRequest = callApi('addValues',saveJson);
        ajaxRequest.done(function(data){
            let jsonData = JSON.parse(data);
                if(typeof(jsonData) === 'object'){
                    jsonData.good.forEach(element => changeToSuccess("input[name='form_"+element+"']"));
				    jsonData.error.forEach(element => changeToError("input[name='form_"+element+"']"));
                }
                else{
                    changeToSuccess($("input[name='form_name']"));
                    changeToSuccess($("input[name='form_age']"));
                    changeToSuccess($("input[name='form_date']"));
                    addInfo('info','User inserted');
                }
        });
    });
    $(document).on('click','#btnVerify',function(){
        let name = $('#form_name').val();
       let age = $('#form_age').val();
       let date = $('#form_date').val();
        let verifyArray = [name,age,date];
        let stringJson = JSON.stringify(verifyArray);

        let ajaxRequest = callApi('verifyValues',stringJson);

        ajaxRequest.done(function(data){
            let jsonData = JSON.parse(data);
            if(jsonData.error.length !== 0){
                jsonData.good.forEach(element => changeToSuccess("input[name='form_"+element+"']"));
                jsonData.error.forEach(element => changeToError("input[name='form_"+element+"']"));
                addInfo('info','Wrong values');
            }
            else{
                changeToSuccess($("input[name='form_name']"));
                changeToSuccess($("input[name='form_age']"));
                changeToSuccess($("input[name='form_date']"));
            }
        });

    });
    $(document).on('click','#btnEdit',function(){
        let id = $('#mod_id').val();
        let name = $('#mod_name').val();
        let age = $('#mod_age').val();
        let date = $('#mod_date').val();
        let arrayData = [id, name, age, date];
        let jsonString = JSON.stringify(arrayData);

        let ajaxRequest = callApi('modifyValues',jsonString);

        ajaxRequest.done(function(data){
            let jsonData = JSON.parse(data);
				//if there's errors in the formulary
				if(typeof(jsonData) === "object"){
                    addInfo('warning','Information couldnt be updated, please check the fields again');
				}
                if(jsonData === true){
                    let now = new Date().toLocaleString();
                    $('#mod_mod_date').val(now);
                    addInfo('success','data updated');
                }
				//if there's an error in the query
				if(jsonData === false){
                    addInfo('danger','Error processing the information');
				}
        });
    });
    $(document).on('click','#btnFind',function(){
        let id = parseInt($('#find_id').val());
        if(isNaN(id)){
            return addInfo('warning','User id not found in the database, please check again');
        }
        let jsonString = JSON.stringify(id);

        ajaxRequest = callApi('findUser',jsonString);
        
        ajaxRequest.done(function(data){
            let jsonData = JSON.parse(data);
            if(jsonData.length === 0){
                return addInfo('warning','User id not found in the database, please try again');
            }
            jsonData.forEach(element => {
                $('#mod_id').attr('value',element['ID']);
                $('#mod_name').attr('value',element['name']);
                $('#mod_age').attr('value',element['age']);
                $('#mod_date').attr('value',element['date']);
                $('#mod_creation_date').attr('value',element['creation_date']);
                $('#mod_mod_date').attr('value',element['mod_date']);
                $('#mod_del_date').attr('value',element['del_date']);
            });
        });
    });

    //if the check/X icon is pressed, change the element.
    $('td:nth-child(8) i').click(function(){
        let id = $(this).closest("tr").find('td:first-child').html().trim();
        //Obtain the delete date and image column
        let mod_date = $(this).closest("tr").find('td:nth-child(6)');
        let date = $(this).closest("tr").find('td:nth-child(7)');
        let icon = $(this).closest("tr").find('td:nth-child(8) i');
        let jsonString = JSON.stringify(id);

        let ajaxRequest = callApi('changeRemoved',jsonString);
        ajaxRequest.done(function(data){
            let jsonData = JSON.parse(data);
            if(jsonData === false){
            return addInfo('danger','Error, process couldnt be done');

            }
            let now = new Date().toLocaleString();
            if(jsonData === "1"){
                icon.removeClass('bi bi-x-lg text-danger');
                icon.addClass('bi bi-check-lg text-success');
                mod_date.html(now);
                date.html(now);
            }
            else{
                icon.removeClass('bi bi-check-lg text-success');
                icon.addClass('bi bi-x-lg text-danger');
                mod_date.html(now);
                date.html('--');
            }
        });
        return false;
    });
    //for nav head
    $(document).on('click','li a',function(){
        let nav = $(this).prop('hash');
        let navId = JSON.stringify(nav);
        callApi('currentNav',navId);
    });
});
