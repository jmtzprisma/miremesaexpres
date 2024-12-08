const linkedPicker1Element = document.getElementById("kt_td_picker_linked_1");
const linked1 = new tempusDominus.TempusDominus(linkedPicker1Element);
const linked2 = new tempusDominus.TempusDominus(document.getElementById("kt_td_picker_linked_2"), {
    useCurrent: false,
});

new tempusDominus.TempusDominus(document.getElementById("kt_td_picker_linked_1"), {
    display: {
    icons: {
        time: "fe fe-clock",
        date: "fe fe-calendar",
        up: "fe fe-chevron-up",
        down: "fe fe-chevron-down",
        previous: "fe fe-chevron-down",
        next: "fe fe-chevron-up",
    },
    }
});

new tempusDominus.TempusDominus(document.getElementById("kt_td_picker_linked_2"), {
    display: {
    icons: {
        time: "fe fe-clock",
        date: "fe fe-calendar",
        up: "fe fe-chevron-up",
        down: "fe fe-chevron-down",
        previous: "fe fe-chevron-down",
        next: "fe fe-chevron-up",
    },
    }
});

//using event listeners
linkedPicker1Element.addEventListener(tempusDominus.Namespace.events.change, (e) => {
    linked2.updateOptions({

        restrictions: {
        minDate: e.detail.date,
        },
    });
});

//using subscribe method
const subscription = linked2.subscribe(tempusDominus.Namespace.events.change, (e) => {
    linked1.updateOptions({
        restrictions: {
        maxDate: e.date,
        },
        
    });
    new tempusDominus.TempusDominus(document.getElementById("kt_td_picker_linked_2"), {
        display: {
        icons: {
            time: "fe fe-clock",
            date: "fe fe-calendar",
            up: "fe fe-chevron-up",
            down: "fe fe-chevron-down",
            previous: "fe fe-chevron-down",
            next: "fe fe-chevron-up",
        },
        }
    });
});