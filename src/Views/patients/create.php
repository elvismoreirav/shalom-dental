<?php
$this->extend('layouts.app');
?>

<?php $this->section('content'); ?>

<!-- Enhanced Header with Context -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm">
                    <li>
                        <a href="/patients" class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                            Pacientes
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-gray-400 mx-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-shalom-primary font-medium">Nuevo Paciente</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                <div class="w-12 h-12 bg-shalom-primary rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                Crear Nuevo Paciente
            </h1>
            <p class="mt-2 text-gray-600">Registra un nuevo paciente en el sistema. La información será utilizada para agendar citas y mantener un historial médico completo.</p>
        </div>
        
        <!-- Quick Stats -->
        <div class="hidden lg:flex items-center space-x-4">
            <div class="bg-shalom-light rounded-lg px-4 py-3 text-center">
                <p class="text-xs text-shalom-primary font-medium">Total Pacientes</p>
                <p class="text-xl font-bold text-shalom-primary">1,234</p>
            </div>
            <div class="bg-green-50 rounded-lg px-4 py-3 text-center">
                <p class="text-xs text-green-600 font-medium">Nuevos este mes</p>
                <p class="text-xl font-bold text-green-600">47</p>
            </div>
        </div>
    </div>
</div>

<!-- Help Tips Card -->
<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium text-blue-800">Consejo para un mejor registro</h3>
            <div class="mt-2 text-sm text-blue-700">
                <ul class="list-disc list-inside space-y-1">
                    <li>Los campos marcados con <span class="text-red-500">*</span> son obligatorios</li>
                    <li>El teléfono es crucial para confirmar citas y enviar recordatorios</li>
                    <li>Las notas médicas ayudan a personalizar el tratamiento dental</li>
                    <li>Puedes completar la información básica ahora y agregar detalles después</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Form -->
<?= $this->include('patients._form', [
    'action' => '/patients',
    'method' => 'POST',
]) ?>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced form validation
    const form = document.querySelector('form[action*="/patients"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validatePatientForm()) {
                e.preventDefault();
                showValidationErrors();
                return false;
            }
        });
    }
    
    // Real-time validation
    const requiredFields = document.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this);
        });
        
        field.addEventListener('input', function() {
            if (this.classList.contains('border-red-500')) {
                validateField(this);
            }
        });
    });
    
    // ID type validation
    const idTypeField = document.getElementById('id_type');
    const idNumberField = document.getElementById('id_number');
    
    if (idTypeField && idNumberField) {
        idTypeField.addEventListener('change', function() {
            updateIdNumberValidation(this.value, idNumberField);
        });
        
        // Initial validation
        updateIdNumberValidation(idTypeField.value, idNumberField);
    }
    
    // Birth date validation (must be reasonable)
    const birthDateField = document.getElementById('birth_date');
    if (birthDateField) {
        birthDateField.addEventListener('change', function() {
            const birthDate = new Date(this.value);
            const today = new Date();
            const minDate = new Date(today.getFullYear() - 120, today.getMonth(), today.getDate());
            const maxDate = new Date(today.getFullYear() - 1, today.getMonth(), today.getDate());
            
            if (birthDate > maxDate || birthDate < minDate) {
                this.setCustomValidity('La fecha de nacimiento debe estar entre 1 y 120 años');
                this.classList.add('border-red-500');
            } else {
                this.setCustomValidity('');
                this.classList.remove('border-red-500');
            }
        });
    }
    
    // Email validation
    const emailField = document.getElementById('email');
    if (emailField) {
        emailField.addEventListener('blur', function() {
            if (this.value && !isValidEmail(this.value)) {
                this.setCustomValidity('Ingrese un correo electrónico válido');
                this.classList.add('border-red-500');
            } else {
                this.setCustomValidity('');
                this.classList.remove('border-red-500');
            }
        });
    }
});

function validatePatientForm() {
    let isValid = true;
    const requiredFields = document.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    
    if (!value) {
        field.classList.add('border-red-500', 'ring-red-500');
        isValid = false;
    } else {
        field.classList.remove('border-red-500', 'ring-red-500');
    }
    
    // Specific validations
    if (field.type === 'email' && value && !isValidEmail(value)) {
        field.classList.add('border-red-500', 'ring-red-500');
        isValid = false;
    }
    
    if (field.type === 'tel' && value && !isValidPhone(value)) {
        field.classList.add('border-red-500', 'ring-red-500');
        isValid = false;
    }
    
    return isValid;
}

function updateIdNumberValidation(idType, idNumberField) {
    if (!idNumberField) return;
    
    // Remove existing validation
    idNumberField.removeAttribute('pattern');
    idNumberField.removeAttribute('title');
    
    switch (idType) {
        case 'cedula':
            idNumberField.setAttribute('pattern', '[0-9]{10}');
            idNumberField.setAttribute('title', 'La cédula debe tener 10 dígitos');
            idNumberField.setAttribute('maxlength', '10');
            break;
        case 'ruc':
            idNumberField.setAttribute('pattern', '[0-9]{13}');
            idNumberField.setAttribute('title', 'El RUC debe tener 13 dígitos');
            idNumberField.setAttribute('maxlength', '13');
            break;
        case 'pasaporte':
            idNumberField.setAttribute('pattern', '[A-Za-z0-9]{6,20}');
            idNumberField.setAttribute('title', 'El pasaporte debe tener entre 6 y 20 caracteres alfanuméricos');
            idNumberField.setAttribute('maxlength', '20');
            break;
        default:
            idNumberField.removeAttribute('pattern');
            idNumberField.removeAttribute('title');
            idNumberField.removeAttribute('maxlength');
    }
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidPhone(phone) {
    const phoneRegex = /^[\d\s\-\+\(\)]+$/;
    return phoneRegex.test(phone) && phone.replace(/\D/g, '').length >= 7;
}

function showValidationErrors() {
    const firstError = document.querySelector('.border-red-500');
    if (firstError) {
        firstError.focus();
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}
</script>
<?php $this->endSection(); ?>