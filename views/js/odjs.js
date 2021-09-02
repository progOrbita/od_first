function changeToSuccess(value){
    $(value).removeClass("bg-error");
    $(value).addClass("bg-fine");
}
function changeToError(value){
    $(value).removeClass("bg-fine");
    $(value).addClass("bg-error");
}
$(document).ready(function(){

    let img0 = '../img/admin/disabled.gif';
    let img1 = '../img/admin/enabled.gif';
                
    $(document).on('click','#btnSubmit',function(){
       let name = $('#name').val();
       let age = $('#age').val();
       let date = $('#date').val();
       let saveArray = [name,age,date];
       let saveJson = JSON.stringify(saveArray);
       let ajaxRequest = $.ajax({
            url: admin_od,
            data: {
                ajax: true,
                action: 'addValues',
                dataString : saveJson,
            },
        });
        ajaxRequest.done(function(data){
            let jsonData = JSON.parse(data);
                if(typeof(jsonData) === 'object'){
                    jsonData.good.forEach(element => changeToSuccess("input[name='"+element+"']"));
				    jsonData.error.forEach(element => changeToError("input[name='"+element+"']"));
                    if(jsonData.error.length !== 0){
                        console.log('data error');
                    }
                }
                else{
                    changeToSuccess($("input[name='name']"));
                    changeToSuccess($("input[name='age']"));
                    changeToSuccess($("input[name='date']"));
                    console.log('saved');
                }
        });
    });
    $(document).on('click','#btnVerify',function(){
        let name = $('#name').val();
       let age = $('#age').val();
       let date = $('#date').val();
        let verifyArray = [name,age,date];
        let stringJson = JSON.stringify(verifyArray);
        $.ajax({
             url: admin_od,
             data: {
                 ajax: true,
                 action: 'verifyValues',
                 dataString : stringJson,
             },
             success : function(result){
                let jsonData = JSON.parse(result);
                jsonData.good.forEach(element => changeToSuccess("input[name='"+element+"']"));
				jsonData.error.forEach(element => changeToError("input[name='"+element+"']"));
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
        //send the data as a JSON string
        let ajaxRequest = $.ajax({
            url: admin_od,
            
            data: {
                ajax: true,
                action: 'modifyValues',
                dataString: jsonString,
            },
        });
        ajaxRequest.done(function(data){
            let jsonData = JSON.parse(data);
				//if there's errors in the formulary
				if(typeof(jsonData) === "object"){
                    //Nullify save button if there's an error
                    jsonData.good.forEach(element => changeToSuccess("input[name='"+element+"']"));
				    jsonData.error.forEach(element => changeToError("input[name='"+element+"']"));
				}
				//if the query is right
				else if(jsonData === true){
				}
				//if there's an error in the query
				else{
				}
        });
    });
    $(document).on('click','#btnFind',function(){
        let id = $('#find_id').val();
        let jsonString = JSON.stringify(id);
        //send the data as a JSON string
        let ajaxRequest = $.ajax({
            url: admin_od,
            data: {
                ajax: true,
                action: 'findUser',
                dataString: jsonString,
            },
        });
        ajaxRequest.done(function(data){
            let jsonData = JSON.parse(data);
            jsonData.forEach(element => {
                $('#mod_id').attr('value',element['ID']);
                $('#mod_name').attr('value',element['name']);
                $('#mod_age').attr('value',element['age']);
                $('#mod_creation_date').attr('value',element['creation_date']);
                $('#mod_mod_date').attr('value',element['mod_date']);
                $('#mod_del_date').attr('value',element['del_date']);
            });
        });
    });
    //if the check/X image is pressed, change the element.
    $('td:nth-child(8) img').click(function(e){
        let id = $(this).closest("tr").find('td:first-child').html().trim();
        //Obtain the delete date and image column
        let mod_date = $(this).closest("tr").find('td:nth-child(6)');
        let date = $(this).closest("tr").find('td:nth-child(7)');
        let image = $(this).closest("tr").find('td:nth-child(8) img');
        e.preventDefault();
        let jsonString = JSON.stringify(id);
        //send the data as a JSON string
        let ajaxRequest = $.ajax({
            url: admin_od,
            data: {
                ajax: true,
                action: 'changeRemoved',
                dataString: jsonString,
            },
        });
        ajaxRequest.done(function(data){
            let jsonData = JSON.parse(data);
            let now = new Date().toLocaleString();
            if(jsonData === "1"){
                image.attr('src',img1);
                mod_date.html(now);
                date.html(now);
            }
            else{
                image.attr('src',img0);
                mod_date.html(now);
                date.html('--');
            }
        });
        return false;
    });
});
