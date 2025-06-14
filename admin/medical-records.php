<?php
include('../conn.php');
session_start();
$records = [];
$query = "
    SELECT 
        mr.*, 
        p.name AS pet_name, 
        po.first_name, po.middle_name, po.last_name
    FROM medical_records mr
    LEFT JOIN pet_records p ON mr.pet_id = p.pet_id
    LEFT JOIN pet_owner_records po ON mr.owner_id = po.owner_id
    ORDER BY mr.medical_record_id DESC
";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $records[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="../assets/images/pethaus_logo.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/style.css">
    <title>Medical Records</title>
</head>

<body>
    <?php include('../components/navbar.php'); ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 text-white min-vh-100 p-0">
                <?php include('../components/sidebar.php'); ?>
            </div>

            <div class="col-md-10 bg-light min-vh-100 py-4 px-3">
                <h3 class="fw-bold mb-3">Medical Records</h3>

                <div class="d-flex justify-content-between mb-5">
                    <div class="w-auto">
                        <div>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-file-medical"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search medical records...">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn bg-black text-white" onclick="location.reload();">
                            <i class="fa-solid fa-arrows-rotate"></i> Refresh
                        </button>
                        <button class="btn bg-black text-white" data-bs-toggle="modal" data-bs-target="#medicalRecordWizard">Add new record</button>
                    </div>

                    <div class="modal fade" id="medicalRecordWizard" tabindex="-1" aria-labelledby="wizardLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <form id="medicalRecordForm" action="../actions/process_medical_record.php" method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold" id="wizardLabel">Create Medical Record</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div id="wizardSteps">

                                            <!-- Step 1: Owner and Pet -->
                                            <div class="wizard-step" data-step="1">
                                                <h5 class="mb-3">Step 1: Select Owner and Pet</h5>
                                                <div class="mb-3">
                                                    <label class="form-label">Owner</label>
                                                    <select name="owner_id" id="ownerSelect" class="form-select" required>
                                                        <option value="">Select Owner</option>
                                                        <?php
                                                        include('../conn.php'); // Adjust path to your DB connection
                                                        $owners = $conn->query("SELECT owner_id, CONCAT(first_name, ' ', last_name) AS owner_name FROM pet_owner_records");
                                                        while ($row = $owners->fetch_assoc()) {
                                                            echo "<option value='{$row['owner_id']}'>{$row['owner_name']}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Pet</label>
                                                    <select name="pet_id" id="petSelect" class="form-select" required disabled>
                                                        <option value="">Select Pet</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Step 2: Medical Record -->
                                            <div class="wizard-step d-none" data-step="2">
                                                <h5 class="mb-3">Step 2: Medical Record</h5>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Type</label>
                                                        <input type="text" class="form-control" name="type" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Start Date</label>
                                                        <input type="date" class="form-control" name="date_started" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">End Date</label>
                                                        <input type="date" class="form-control" name="date_ended" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Weight</label>
                                                        <input type="text" class="form-control" name="weight" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Temperature (°C)</label>
                                                        <input type="text" class="form-control" name="temperature" required>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label">Complaint</label>
                                                        <textarea class="form-control" name="complaint" rows="2" required></textarea>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label">Description</label>
                                                        <textarea class="form-control" name="description" rows="2" required></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Step 3: Treatment -->
                                            <div class="wizard-step d-none" data-step="3">
                                                <h5 class="mb-3">Step 3: Treatment</h5>

                                                <button type="button" class="btn bg-black text-white mb-3" id="addTreatmentBtn">+ Add Treatment</button>

                                                <div id="treatmentForm" class="d-none">
                                                    <div class="row g-2 mb-2">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Date</label>
                                                            <input type="date" name="treatment_date" class="form-control">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Name</label>
                                                            <input type="text" name="treatment_name" class="form-control">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Test</label>
                                                            <input type="text" name="treatment_test" class="form-control">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Remarks</label>
                                                            <input type="text" name="treatment_remarks" class="form-control">
                                                        </div>
                                                        <div class="col-md-3 mt-2">
                                                            <label class="form-label">Charge (₱)</label>
                                                            <input type="number" step="0.01" name="treatment_charge" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Step 4: Prescription -->
                                            <div class="wizard-step d-none" data-step="4">
                                                <h5 class="mb-3">Step 4: Prescription</h5>

                                                <button type="button" class="btn bg-black text-white mb-3" id="addPrescriptionBtn">+ Add Prescription</button>

                                                <div id="prescriptionForm" class="d-none">
                                                    <div class="row g-2 mb-2">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Date</label>
                                                            <input type="date" name="prescription_date" class="form-control">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Name</label>
                                                            <input type="text" name="prescription_name" class="form-control">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Description</label>
                                                            <input type="text" name="prescription_description" class="form-control">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Remarks</label>
                                                            <input type="text" name="prescription_remarks" class="form-control">
                                                        </div>
                                                        <div class="col-md-3 mt-2">
                                                            <label class="form-label">Charge (₱)</label>
                                                            <input type="number" step="0.01" name="prescription_charge" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Step 5: Others -->
                                            <div class="wizard-step d-none" data-step="5">
                                                <h5 class="mb-3">Step 5: Other Charges</h5>

                                                <button type="button" class="btn bg-black text-white mb-3" id="addOthersBtn">+ Add Other</button>

                                                <div id="othersForm" class="d-none">
                                                    <div class="row g-2 mb-2">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Date</label>
                                                            <input type="date" name="others_date" class="form-control">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Name</label>
                                                            <input type="text" name="others_name" class="form-control">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">Quantity</label>
                                                            <input type="text" name="others_quantity" class="form-control">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">Remarks</label>
                                                            <input type="text" name="others_remarks" class="form-control">
                                                        </div>
                                                        <div class="col-md-2 mt-2">
                                                            <label class="form-label">Charge (₱)</label>
                                                            <input type="number" step="0.01" name="others_charge" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer flex-column gap-2">
                                        <div class="form-check d-flex justify-content-start gap-3 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="create_bill" id="createBillCheckbox" value="1">
                                                <label class="form-check-label" for="createBillCheckbox">Create Bill</label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="print_bill" id="printBillCheckbox" value="1">
                                                <label class="form-check-label" for="printBillCheckbox">Print Bill after submission</label>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between w-100">
                                            <button type="button" class="btn bg-black text-white" id="prevBtn" disabled>Back</button>
                                            <button type="button" class="btn bg-black text-white" id="nextBtn">Next</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">TYPE</th>
                                <th scope="col">START DATE</th>
                                <th scope="col">END DATE</th>
                                <th scope="col">DESCRIPTION</th>
                                <th scope="col">WEIGHT</th>
                                <th scope="col">TEMPERATURE</th>
                                <th scope="col">PET</th>
                                <th scope="col">OWNER</th>
                                <th scope="col">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($records)) : ?>
                                <?php foreach ($records as $rec) : ?>
                                    <tr>
                                        <td class="p-3"><?php echo $rec['medical_record_id']; ?></td>
                                        <td class="p-3"><?php echo $rec['type']; ?></td>
                                        <td class="p-3"><?php echo date('F j, Y', strtotime($rec['date_started'])); ?></td>
                                        <td class="p-3"><?php echo date('F j, Y', strtotime($rec['date_ended'])); ?></td>
                                        <td class="p-3"><?php echo $rec['description']; ?></td>
                                        <td class="p-3"><?php echo $rec['weight']; ?></td>
                                        <td class="p-3"><?php echo $rec['temperature']; ?></td>
                                        <td class="p-3"><?php echo $rec['pet_name']; ?></td>
                                        <td class="p-3">
                                            <?php
                                            echo $rec['first_name'] . ' ';
                                            echo !empty($rec['middle_name']) ? $rec['middle_name'][0] . '. ' : '';
                                            echo $rec['last_name'];
                                            ?>
                                        </td>
                                        <td class="p-3 d-flex alignt-items-center gap-3">
                                            <a href="../actions/view_bill.php?record_id=<?php echo $rec['medical_record_id']; ?>" class="text-black"><i class="fa-solid fa-file-medical"></i></a>

                                            <a href="../actions/view_medical.php?id=<?php echo $rec['medical_record_id']; ?>" class="text-black"><i class="fa-solid fa-file-invoice-dollar"></i></a>

                                            <!-- <a href="edit_medical.php?id=<?php echo $rec['medical_record_id']; ?>" class="text-black"><i class="fa-solid fa-pen-to-square"></i></a> -->
                                            <i class="fa-solid fa-x"></i>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="10" class="text-center">No medical records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;

        document.getElementById('nextBtn').addEventListener('click', () => {
            const currentStepEl = document.querySelector(`[data-step="${currentStep}"]`);

            // Validate current step
            const inputs = currentStepEl.querySelectorAll('input, select, textarea');
            for (let input of inputs) {
                if (input.hasAttribute('required') && !input.value.trim()) {
                    input.classList.add('is-invalid');
                    return;
                } else {
                    input.classList.remove('is-invalid');
                }
            }

            if (currentStep < 5) {
                currentStepEl.classList.add('d-none');
                currentStep++;
                document.querySelector(`[data-step="${currentStep}"]`).classList.remove('d-none');
                document.getElementById('prevBtn').disabled = false;
                if (currentStep === 5) {
                    document.getElementById('nextBtn').textContent = 'Submit';
                }
            } else {
                // Final Submit
                if (document.getElementById('printBillCheckbox').checked) {
                    // Use a temporary target to open in a new tab
                    const form = document.getElementById('medicalRecordForm');
                    form.target = '_blank'; // open in new tab
                    form.submit();
                    form.target = ''; // reset target
                    
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    document.getElementById('medicalRecordForm').submit();
                }
            }
        });

        document.getElementById('prevBtn').addEventListener('click', () => {
            if (currentStep > 1) {
                document.querySelector(`[data-step="${currentStep}"]`).classList.add('d-none');
                currentStep--;
                document.querySelector(`[data-step="${currentStep}"]`).classList.remove('d-none');
                document.getElementById('nextBtn').textContent = 'Next';
            }
            if (currentStep === 1) {
                document.getElementById('prevBtn').disabled = true;
            }
        });

        document.getElementById('addTreatmentBtn').addEventListener('click', function() {
            document.getElementById('treatmentForm').classList.remove('d-none');
            this.classList.add('d-none');
        });

        document.getElementById('addPrescriptionBtn').addEventListener('click', function() {
            document.getElementById('prescriptionForm').classList.remove('d-none');
            this.classList.add('d-none');
        });

        document.getElementById('addOthersBtn').addEventListener('click', function() {
            document.getElementById('othersForm').classList.remove('d-none');
            this.classList.add('d-none');
        });

        document.getElementById('ownerSelect').addEventListener('change', function() {
            const ownerId = this.value;
            const petSelect = document.getElementById('petSelect');
            petSelect.innerHTML = '<option value="">Loading...</option>';
            petSelect.disabled = true;

            if (ownerId) {
                fetch(`../actions/get_pets.php?owner_id=${ownerId}`)
                    .then(response => response.json())
                    .then(data => {
                        petSelect.innerHTML = '<option value="">Select Pet</option>';
                        data.forEach(pet => {
                            const option = document.createElement('option');
                            option.value = pet.pet_id;
                            option.textContent = pet.name;
                            petSelect.appendChild(option);
                        });
                        petSelect.disabled = false;
                    })
                    .catch(err => {
                        petSelect.innerHTML = '<option value="">Error loading pets</option>';
                    });
            } else {
                petSelect.innerHTML = '<option value="">Select Pet</option>';
                petSelect.disabled = true;
            }
        });

        document.getElementById('searchInput').addEventListener('input', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('table tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const match = text.includes(searchValue);
                row.style.display = match ? '' : 'none';
            });
        });
    </script>

    <?php include('../components/toast.php'); ?>
    <?php include('../components/script.php'); ?>
</body>

</html>