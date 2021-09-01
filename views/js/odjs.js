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
    });
});
