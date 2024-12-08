$(function() {
    function clearDataForm(dTable, btnClear) {
        btnClear.attr("disabled", "disabled")
        dTable.empty();
    }

    function addRates(dTable, btnClear, oRate) {
        btnClear.removeAttr("disabled");

        var rateSelect = rates.find(element => element.id.toString() === oRate.val());

        const ratesFilter = rates.filter(element => element.type === rateSelect.type 
            && element.id >= parseInt(oRate.val()));

        if (ratesFilter.length > 15) {
            var i = 0;
            var percentage = 100/15;
            var end_percentage = 0;
            var initial_percentage = 0;

            ratesFilter.map(element => {
                if (element.id >= rateSelect.id && i < 15) {
                    end_percentage += percentage;
                    end_percentage = parseInt(end_percentage);
                    i++;
                    if (i > 14) end_percentage = 100;

                    dTable.append("<tr>" +
                            "<td style='width:20%'>" + 
                                element.name +" %"+initial_percentage+"-"+end_percentage +
                                "<input type='hidden' name='"+dTable.attr('id')+"["+i+"][rate_id]' value='"+element.id+"' />" +
                            "</td>"  +
                            "<td style='width:40%'>" + 
                                "<input type='text' name='"+dTable.attr('id')+"["+i+"][start_percent]' value='"+initial_percentage+"' />" +
                            "</td>"  +
                            "<td style='width:40%'>" + 
                                "<input type='text' name='"+dTable.attr('id')+"["+i+"][end_percent]' value='"+end_percentage+"' />" +
                            "</td>");

                    initial_percentage += percentage;
                    initial_percentage = parseInt(initial_percentage);
                }
            })
        } else {
            var i = 0;
            var percentage = 100/ratesFilter.length;
            var end_percentage = 0;
            var initial_percentage = 0;
            var endRecords = ratesFilter.length;

            ratesFilter.map(element => {
                if (element.id >= rateSelect.id && i <= endRecords) {
                    end_percentage += percentage;
                    end_percentage = parseInt(end_percentage);
                    i++;
                    if (i == endRecords) end_percentage = 100;

                    dTable.append("<tr>" +
                            "<td style='width:20%'>" + 
                                element.name +" %"+initial_percentage+"-"+end_percentage +
                                "<input type='hidden' name='"+dTable.attr('id')+"["+i+"][rate_id]'  value='"+element.id+"' />" +
                            "</td>"  +
                            "<td style='width:40%'>" + 
                                "<input type='text' name='"+dTable.attr('id')+"["+i+"][start_percent]' value='"+initial_percentage+"' />" +
                            "</td>"  +
                            "<td style='width:40%'>" + 
                                "<input type='text' name='"+dTable.attr('id')+"["+i+"][end_percent]' value='"+end_percentage+"' />" +
                            "</td>");

                    initial_percentage += percentage;
                    initial_percentage = parseInt(initial_percentage);
                }
            })
        }
    }

    function validateForm() {
        var form = document.getElementById("frmRates");
        form.addEventListener('submit', function(event) {
            if (form.checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    }

    function initial() {
        $("#ancipation_121_button_clean").attr("disabled", "disabled")
        $("#ancipation_120_90_button_clean").attr("disabled", "disabled")
        $("#ancipation_89_60_button_clean").attr("disabled", "disabled")
        $("#ancipation_59_30_button_clean").attr("disabled", "disabled")
        $("#ancipation_29_15_button_clean").attr("disabled", "disabled")
        $("#ancipation_14_7_button_clean").attr("disabled", "disabled")
        $("#ancipation_6_3_button_clean").attr("disabled", "disabled")
        $("#ancipation_2_4h_button_clean").attr("disabled", "disabled")
        $("#ancipation_4h_button_clean").attr("disabled", "disabled")

        $("#ancipation_121_button_clean").click(e => {
            e.preventDefault();
            clearDataForm($("#ancipation_121_table"), $("#ancipation_121_button_clean"));
        });
        $("#ancipation_120_90_button_clean").click(e => {
            e.preventDefault();
            clearDataForm($("#ancipation_120_90_table"), $("#ancipation_120_90_button_clean"));
        });
        $("#ancipation_89_60_button_clean").click(e => {
            e.preventDefault();
            clearDataForm($("#ancipation_89_60_table"), $("#ancipation_89_60_button_clean"));
        });
        $("#ancipation_59_30_button_clean").click(e => {
            e.preventDefault();
            clearDataForm($("#ancipation_59_30_table"), $("#ancipation_59_30_button_clean"));
        });
        $("#ancipation_29_15_button_clean").click(e => {
            e.preventDefault();
            clearDataForm($("#ancipation_29_15_table"), $("#ancipation_29_15_button_clean"));
        });
        $("#ancipation_14_7_button_clean").click(e => {
            e.preventDefault();
            clearDataForm($("#ancipation_14_7_table"), $("#ancipation_14_7_button_clean"));
        });
        $("#ancipation_6_3_button_clean").click(e => {
            e.preventDefault();
            clearDataForm($("#ancipation_6_3_table"), $("#ancipation_6_3_button_clean"));
        });
        $("#ancipation_2_4h_button_clean").click(e => {
            e.preventDefault();
            clearDataForm($("#ancipation_2_4h_table"), $("#ancipation_2_4h_button_clean"));
        });
        $("#ancipation_4h_button_clean").click(e => {
            e.preventDefault();
            clearDataForm($("#ancipation_4h_table"), $("#ancipation_4h_button_clean"));
        });

        $("#ancipation_121_button").click(e => {
            e.preventDefault();
            addRates($("#ancipation_121_table"), $("#ancipation_121_button_clean"), $("#ancipation_121"));
        });
        $("#ancipation_120_90_button").click(e => {
            e.preventDefault();
            addRates($("#ancipation_120_90_table"), $("#ancipation_120_90_button_clean"), $("#ancipation_120_90"));
        });
        $("#ancipation_89_60_button").click(e => {
            e.preventDefault();
            addRates($("#ancipation_89_60_table"), $("#ancipation_89_60_button_clean"), $("#ancipation_89_60"));
        });
        $("#ancipation_59_30_button").click(e => {
            e.preventDefault();
            addRates($("#ancipation_59_30_table"), $("#ancipation_59_30_button_clean"), $("#ancipation_59_30"));
        });
        $("#ancipation_29_15_button").click(e => {
            e.preventDefault();
            addRates($("#ancipation_29_15_table"), $("#ancipation_29_15_button_clean"), $("#ancipation_29_15"));
        });
        $("#ancipation_14_7_button").click(e => {
            e.preventDefault();
            addRates($("#ancipation_14_7_table"), $("#ancipation_14_7_button_clean"), $("#ancipation_14_7"));
        });
        $("#ancipation_6_3_button").click(e => {
            e.preventDefault();
            addRates($("#ancipation_6_3_table"), $("#ancipation_6_3_button_clean"), $("#ancipation_6_3"));
        });
        $("#ancipation_2_4h_button").click(e => {
            e.preventDefault();
            addRates($("#ancipation_2_4h_table"), $("#ancipation_2_4h_button_clean"), $("#ancipation_2_4h"));
        });
        $("#ancipation_4h_button").click(e => {
            e.preventDefault();
            addRates($("#ancipation_4h_table"), $("#ancipation_4h_button_clean"), $("#ancipation_4h"));
        });

        $('.select2').select2({
            minimumResultsForSearch: Infinity
        });

        if ($("#type").val() == "Tarifa")  {
            $("#div_cost").show();
            $("#div_anticipations").hide();
            $("#cost").attr("required", "required");
        } else {
            $("#div_cost").hide();
            $("#div_anticipations").show();
            $("#cost").removeAttr("required");
        }

        validateForm();

        $("#type").change(() => {
            if ($("#type").val() == "Tarifa")  {
                $("#div_cost").show();
                $("#div_anticipations").hide();
                $("#cost").attr("required", "required");
            } else {
                $("#div_cost").hide();
                $("#div_anticipations").show();
                $("#cost").removeAttr("required");
            }
        });
    }

    $(document).ready(initial);
});
