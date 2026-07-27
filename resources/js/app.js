import './bootstrap';
import Alpine from 'alpinejs';

import TomSelect from "tom-select";
import "tom-select/dist/css/tom-select.css";

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".tom-select").forEach((el) => {
        new TomSelect(el, {
            create: false,
            maxItems: 1,
            placeholder: "Cari kelas...",
            sortField: {
                field: "text",
                direction: "asc",
            },
        });
    });
});

window.Alpine = Alpine;
Alpine.start();