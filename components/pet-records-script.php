<script>
    function toggleOtherSpeciesInput() {
        const speciesSelect = document.getElementById('species');
        const otherContainer = document.getElementById('otherSpeciesContainer');
        const selectedSpecies = speciesSelect.value;

        if (selectedSpecies === 'Others') {
            otherContainer.classList.remove('d-none');
        } else {
            otherContainer.classList.add('d-none');
            document.getElementById('other_species').removeAttribute('required');
        }
    }

    const speciesSelect = document.getElementById('species');
    const breedContainer = document.getElementById('breed-container');

    speciesSelect.addEventListener('change', function() {
        const selectedSpecies = this.value;
        breedContainer.innerHTML = '';

        const label = document.createElement('label');
        label.textContent = 'Breed';
        label.className = 'form-label';
        label.setAttribute('for', 'breed');

        if (selectedSpecies === 'Dog' || selectedSpecies === 'Cat') {
            const select = document.createElement('select');
            select.name = 'breed';
            select.id = 'breed';
            select.className = 'form-select';

            // Create default option
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Select breed';
            select.appendChild(defaultOption);

            // Append label and select to the container
            breedContainer.appendChild(label);
            breedContainer.appendChild(select);

            // Fetch and populate breeds
            fetch(`../actions/get_breeds.php?species=${selectedSpecies}`)
                .then(response => response.json())
                .then(breeds => {
                    breeds.forEach(breed => {
                        const option = document.createElement('option');
                        option.value = breed;
                        option.textContent = breed;
                        select.appendChild(option);
                    });

                    // Initialize Select2 on this select after populating
                    $(select).select2({
                        placeholder: "Select breed",
                        tags: true,
                        allowClear: true,
                        dropdownParent: $('#addNewPet')
                    });
                })
                .catch(error => {
                    console.error('Error fetching breeds:', error);
                });
        } else {
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control';
            input.placeholder = 'Enter breed';
            input.name = 'breed';
            input.id = 'breed';

            breedContainer.appendChild(label);
            breedContainer.appendChild(input);
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const currentSpecies = speciesSelect.value;
        if (currentSpecies) {
            speciesSelect.dispatchEvent(new Event('change'));
        }
    });
</script>