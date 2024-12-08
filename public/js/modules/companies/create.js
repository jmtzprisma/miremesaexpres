function addInputs(){
    const div = document.querySelector(".inputs");
    let value = document.getElementById('department');
    let valor = value.options[value.selectedIndex].text;
    
    if(value.options[value.selectedIndex].value != 0){
        div.innerHTML += "  <div class='col-md-6 mb-3'><label for='telefono'> Telefono "+valor+"</label> <input class='form-control' type='number' name='phone[]' id='telefono' placeholder='Ingrese el telefono'></div><div class='col-md-6 mb-3'><label for='email'> Email "+valor+"</label>  <input class='form-control' type='email' name='mail[]' id='email' placeholder='Ingrese el email'></div>"; // Interpreta el HTML
        div.innerHTML;    // "<strong>Importante</strong>"
    }
}

function addClientInf(val){
    const div = document.querySelector(".clientInf");

    if (val == 'phone'){
        div.innerHTML += "  <div class='col-md-6 mb-3'><label for='telefono'> Telefono Informacion al Cliente </label>  <input class='form-control' type='number' name='client_phone[]' id='client_phone' placeholder='Ingrese el telefono del cliente'></div>"; // Interpreta el HTML
        div.innerHTML;    // "<strong>Importante</strong>"
    }else{
        div.innerHTML += "     <div class='col-md-6 mb-3'><label for='client_email'> Email Contacto Cliente </label> <input class='form-control' type='email' name='client_email[]' id='client_email' placeholder='Ingrese el email del cliente'></div>"; // Interpreta el HTML
        div.innerHTML;    // "<strong>Importante</strong>"
   
    }

}

$(function() {
    window.addEventListener('load', function() {
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.getElementsByClassName('needs-validation');
        console.log(forms);
        // Loop over them and prevent submission
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);

    function showPreview() {
        const file = $("#logo")[0].files[0];

        if (file) {
            $("#imgPreview").attr("src", URL.createObjectURL(file));
        }
    }

    function initial() {
        $('.select2').select2({
            minimumResultsForSearch: Infinity
        });

        $(function() {
            $("#accordion").accordion(
                {
                    heightStyle: "content"
                }
            );
        });

        $('#is_parking').change(function() {
            console.log("this.checked", this.checked);
            if(this.checked) {
                $('#title_valet').show();
                $('#form_valet').show();
            } else {
                $('#title_valet').hide();
                $('#form_valet').hide();
            }
        });
        
        if (!$('#is_parking').is(':checked')) {
            $('#title_valet').hide();
            $('#form_valet').hide();
        }

        $("#logo").change(showPreview);
    }
    
    function storeDepartment() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var url = "/department/store";
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                "name": $("#nameDepartment").val(),
            },
        }).done(function() {
            $('#modaldepartment').modal('toggle');

            notif({
				msg: "<b>Registro:</b> Se registro el departamento correctamente",
				type: "success"
			});

            getDepartments();
        }).fail(function(data) {
            if (data.responseJSON.msg !== undefined) {
                divModalErrorShowVehicle(data.responseJSON.msg);
            } else {
                showErrorsVehicle(data.responseJSON.errors);
            }
            hideLoading();
        });
  
    }

    function getDepartments() {
            const url = "/departments/getDepartment";
            $("#department").empty();
            var newOption = new Option("--- Elija Departamento----", "0", true, true);
            $('#department').append(newOption);

            $.get(url, function(data) {
                console.log(data);
                data.map(element => {
                    const description = element.name;
                    var newOption = new Option(description, element.id, false, false);
                    $('#department').append(newOption);
                });

                $('#department').trigger('change')
            });
        }
    

    $('#frmRegisterDepartment').validator().on('submit', function (e) {
        if (e.isDefaultPrevented()) {
        // handle the invalid form...
        } else {
            e.preventDefault();

            if ($("#nameDepartment").val() != "0") {
                storeDepartment();
            } else {
                alert('chao');
            }
        }
    });

    $(document).ready(initial);
});
