$(function() {
    function getAreas() {
        const url = "/companies/" + $("#company_id").val() + "/areas";
        $("#area_id").empty();

        $.get(url, function(data) {
            data.data.map(element => {
                const description = element.name;
                var newOption = new Option(description, element.id, false, false);
                $('#area_id').append(newOption);
            });

            $('#area_id').trigger('change')
        });
    }

    function initial() {
        $('.select2').select2({
            minimumResultsForSearch: Infinity
        });

        $("#company_id").change(() => {
            console.log("entra");
            getAreas();
        });
    }

    $(document).ready(initial);
});
