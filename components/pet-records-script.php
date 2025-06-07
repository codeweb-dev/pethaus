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

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Select breed';
            select.appendChild(defaultOption);

            breedContainer.appendChild(label);
            breedContainer.appendChild(select);

            fetch(`../actions/get_breeds.php?species=${selectedSpecies}`)
                .then(response => response.json())
                .then(breeds => {
                    breeds.forEach(breed => {
                        const option = document.createElement('option');
                        option.value = breed;
                        option.textContent = breed;
                        select.appendChild(option);
                    });

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

    function startWebcam() {
        Webcam.set({
            width: 320,
            height: 240,
            image_format: 'jpeg',
            jpeg_quality: 90,
        });
        Webcam.attach('#my_camera');
        document.getElementById('my_camera').style.display = 'block';
    }

    function stopWebcam() {
        Webcam.reset();
        document.getElementById('results').innerHTML = '';
        document.getElementById('captured_image').value = '';
        document.getElementById('my_camera').style.display = 'none';
    }

    function takeSnapshot() {
        Webcam.snap(function(data_uri) {
            document.getElementById('results').innerHTML = '<img src="' + data_uri + '" class="img-fluid"/>';
            document.getElementById('captured_image').value = data_uri;
            Webcam.reset();
        });
        document.getElementById('my_camera').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const currentSpecies = speciesSelect.value;
        const uploadSection = document.getElementById('upload_section');
        const captureSection = document.getElementById('capture_section');
        const radioButtons = document.querySelectorAll('input[name="photo_option"]');

        if (currentSpecies) {
            speciesSelect.dispatchEvent(new Event('change'));
        }

        radioButtons.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'upload') {
                    uploadSection.classList.remove('d-none');
                    captureSection.classList.add('d-none');
                } else if (this.value === 'capture') {
                    captureSection.classList.remove('d-none');
                    uploadSection.classList.add('d-none');
                }
            });
        });
    });
</script>