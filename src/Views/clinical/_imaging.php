<!-- Dental Imaging Integration Tab -->
<div class="space-y-4">
    <!-- Imaging Header -->
    <div class="bg-white shadow rounded-lg border border-gray-100">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-shalom-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0l8.586 8.586a2 2 0 012.828 0l-8.586-8.586a2 2 0 01-2.828 0L4 16z"/>
                    <rect x="3" y="6" width="2" height="8" fill="currentColor"/>
                </svg>
                Imágenes y Radiografías Dentales
            </h2>
            <div class="flex items-center gap-2">
                <button @click="imagingMode = 'upload'" 
                        :class="imagingMode === 'upload' ? 'bg-shalom-primary text-white' : 'bg-gray-100 text-gray-600'"
                        class="px-3 py-1 text-xs font-medium rounded-lg transition-colors">
                    📤 Subir Imágenes
                </button>
                <button @click="imagingMode = 'capture'" 
                        :class="imagingMode === 'capture' ? 'bg-shalom-primary text-white' : 'bg-gray-100 text-gray-600'"
                        class="px-3 py-1 text-xs font-medium rounded-lg transition-colors">
                    📸 Capturar desde Cámara
                </button>
                <button @click="imagingMode = 'view'" 
                        :class="imagingMode === 'view' ? 'bg-shalom-primary text-white' : 'bg-gray-100 text-gray-600'"
                        class="px-3 py-1 text-xs font-medium rounded-lg transition-colors">
                    👁️ Ver Archivos
                </button>
            </div>
        </div>

        <div class="p-4">
            <!-- Upload Mode -->
            <div x-show="imagingMode === 'upload'" x-cloak>
                <div class="border-2 border-dashed border-shalom-primary/50 rounded-lg p-6 text-center">
                    <svg class="w-12 h-12 text-shalom-primary mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.8 0l4 4v0.01M7 16a4 4 0 01-.8 0l4-4v0.01M15 8a4 4 0 00-8 0v8H1a1 1 0 00-1 1v-8h8v8h10z"/>
                        <circle cx="8" cy="8" r="1" fill="currentColor"/>
                        <circle cx="15" cy="6" r="1" fill="currentColor"/>
                    </svg>
                    <p class="text-gray-600 mb-3">Arrastra y suelta las imágenes aquí</p>
                    
                    <!-- Hidden file input -->
                    <input type="file" 
                           x-ref="fileInput"
                           multiple 
                           accept="image/*,.pdf"
                           @change="handleFileUpload($event)"
                           class="hidden">
                    
                    <button @click="$refs.fileInput.click()" 
                            class="px-6 py-3 bg-shalom-primary text-white rounded-lg hover:bg-shalom-dark transition-colors">
                        Seleccionar Archivos
                    </button>
                    
                    <div class="mt-3 text-xs text-gray-500">
                        Formatos aceptados: JPG, PNG, GIF, PDF, DICOM
                    </div>
                </div>
                
                <!-- Upload Progress -->
                <div x-show="uploadProgress.show" x-cloak class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-700">Subiendo: <span x-text="uploadProgress.file"></span></span>
                        <span class="text-sm text-gray-500"><span x-text="uploadProgress.percent"></span>%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-shalom-primary h-2 rounded-full transition-all duration-300" 
                             :style="'width: ' + uploadProgress.percent + '%'"></div>
                    </div>
                </div>
            </div>

            <!-- Capture Mode -->
            <div x-show="imagingMode === 'capture'" x-cloak>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                    <div class="text-center mb-4">
                        <svg class="w-12 h-12 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 00-2 2v6a2 2 0 002 2h6a2 2 0 002-2V11a2 2 0 00-2-2H5a2 2 0 00-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M23 19l-6-6m6 6V5a2 2 0 00-2-2h-4a2 2 0 00-2-2v14a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-gray-600 mb-3">Conecta una cámara digital o dispositivo de radiografía</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <button @click="captureImage()" 
                                    class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 00-2 2v6a2 2 0 002 2h6a2 2 0 002 2V11a2 2 0 00-2-2H5a2 2 0 00-2-2V9z"/>
                                </svg>
                                Capturar Imagen
                            </button>
                        </div>
                        
                        <div>
                            <button @click="selectCamera()" 
                                    class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A2 2 0 011.653 0l11.314 7.901a2 2 0 01-1.653-2.276L15 10z"/>
                                </svg>
                                Seleccionar Cámara
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-700 text-center">
                            <strong>Tip:</strong> Para radiografías intraorales, usa la cámara con modo macro y buen iluminación
                        </p>
                    </div>
                </div>
            </div>

            <!-- View Mode -->
            <div x-show="imagingMode === 'view'" x-cloak>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <!-- Imaging Categories -->
                    <div class="col-span-full">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Tipos de Imágenes</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            <button @click="filterByType('all')" 
                                    :class="selectedType === 'all' ? 'bg-shalom-primary text-white' : 'bg-gray-100 text-gray-600'"
                                    class="px-3 py-2 text-xs font-medium rounded-lg transition-colors">
                                Todas
                            </button>
                            <button @click="filterByType('periapical')" 
                                    :class="selectedType === 'periapical' ? 'bg-shalom-primary text-white' : 'bg-gray-100 text-gray-600'"
                                    class="px-3 py-2 text-xs font-medium rounded-lg transition-colors">
                                Periapical
                            </button>
                            <button @click="filterByType('bitewing')" 
                                    :class="selectedType === 'bitewing' ? 'bg-shalom-primary text-white' : 'bg-gray-100 text-gray-600'"
                                    class="px-3 py-2 text-xs font-medium rounded-lg transition-colors">
                                Mordida
                            </button>
                            <button @click="filterByType('panoramic')" 
                                    :class="selectedType === 'panoramic' ? 'bg-shalom-primary text-white' : 'bg-gray-100 text-gray-600'"
                                    class="px-3 py-2 text-xs font-medium rounded-lg transition-colors">
                                Panorámica
                            </button>
                            <button @click="filterByType('cephalometric')" 
                                    :class="selectedType === 'cephalometric' ? 'bg-shalom-primary text-white' : 'bg-gray-100 text-gray-600'"
                                    class="px-3 py-2 text-xs font-medium rounded-lg transition-colors">
                                Cefalométrica
                            </button>
                        </div>
                    </div>
                    
                    <!-- Recent Images Grid -->
                    <div class="col-span-full">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-gray-700">Imágenes Recientes</h3>
                            <div class="flex items-center gap-2">
                                <button @click="sortOrder = 'date'" 
                                        :class="sortOrder === 'date' ? 'bg-shalom-primary text-white' : 'bg-gray-100 text-gray-600'"
                                        class="px-2 py-1 text-xs font-medium rounded">
                                    Fecha
                                </button>
                                <button @click="sortOrder = 'type'" 
                                        :class="sortOrder === 'type' ? 'bg-shalom-primary text-white' : 'bg-gray-100 text-gray-600'"
                                        class="px-2 py-1 text-xs font-medium rounded">
                                    Tipo
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <template x-for="image in filteredImages()" :key="image.id">
                            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-all duration-300 cursor-pointer group"
                                 @click="viewImage(image)">
                                <div class="aspect-w-16 h-32 bg-gray-100 relative">
                                    <img :src="image.thumbnail" :alt="image.description" 
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                    
                                    <!-- Image Type Badge -->
                                    <div class="absolute top-2 left-2">
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full"
                                              :class="getImageTypeClass(image.type)">
                                            <span x-text="image.type_name"></span>
                                        </span>
                                    </div>
                                    
                                    <!-- Date Badge -->
                                    <div class="absolute top-2 right-2">
                                        <span class="px-2 py-0.5 text-xs bg-gray-800/80 text-white rounded">
                                            <span x-text="image.formatted_date"></span>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="p-3">
                                    <h4 class="text-sm font-medium text-gray-900 truncate" x-text="image.description || 'Sin descripción'"></h4>
                                    <p class="text-xs text-gray-500">
                                        <span x-show="image.tooth_number">Diente: <span x-text="image.tooth_number"></span> •</span>
                                        <span x-text="image.file_size"></span>
                                    </p>
                                </div>
                            </div>
                        </template>
                        
                        <div x-show="filteredImages().length === 0" 
                             class="col-span-full text-center py-12">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0l8.586 8.586a2 2 0 012.828 0l-8.586-8.586a2 2 0 01-2.828 0L4 16z"/>
                            </svg>
                            <p class="text-gray-500">No hay imágenes disponibles</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Viewer Modal -->
    <div x-show="selectedImage" x-cloak 
         class="fixed inset-0 bg-black/75 z-50 flex items-center justify-center p-4"
         @click.self="selectedImage = null">
        <div class="bg-white rounded-lg max-w-4xl max-h-full overflow-hidden"
             @click.stop>
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Visor de Imagen</h3>
                <div class="flex items-center gap-3">
                    <button @click="rotateImage(-90)" 
                            class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5a.586.586 0 001.07-1.414l1.293 1.293A2 2 0 013.414 0L8 7v5a.586.586 0 00-1.07-1.414l-1.293 1.293A2 2 0 01-1.414-2L6 12z"/>
                        </svg>
                    </button>
                    <button @click="zoomIn()" 
                            class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                    <button @click="zoomOut()" 
                            class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m6 6V5a2 2 0 00-2-2h-4a2 2 0 00-2-2v14a2 2 0 002 2z"/>
                        </svg>
                    </button>
                    <button @click="selectedImage = null" 
                            class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="relative bg-gray-900 overflow-hidden" style="max-height: 70vh;">
                <img :src="selectedImage.url" 
                     :alt="selectedImage.description" 
                     :style="`transform: scale(${zoomLevel}) rotate(${rotation}deg); transition: all 0.3s ease;`"
                     class="max-w-full max-h-full object-contain">
            </div>
            
            <div class="bg-white p-4 border-t">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="font-medium text-gray-900" x-text="selectedImage.description || 'Sin descripción'"></h4>
                        <div class="text-sm text-gray-500 mt-1">
                            <span x-show="selectedImage.tooth_number">Diente: <span x-text="selectedImage.tooth_number"></span></span>
                            <span x-show="selectedImage.type_name"> • Tipo: <span x-text="selectedImage.type_name"></span></span>
                            <span x-show="selectedImage.date"> • Fecha: <span x-text="selectedImage.formatted_date"></span></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="downloadImage()" 
                                class="px-4 py-2 bg-shalom-primary text-white rounded-lg hover:bg-shalom-dark transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0v6m0-6h6m-6 0v6h6m2 5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Descargar
                        </button>
                        <button @click="addToPatientRecord()" 
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 4v6m2 5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Agregar a Expediente
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function imagingComponent() {
    return {
        imagingMode: 'view',
        selectedType: 'all',
        sortOrder: 'date',
        selectedImage: null,
        images: [
            // Mock data - in real implementation this would come from database
            {
                id: 1,
                type: 'periapical',
                type_name: 'Periapical',
                description: 'Radiografía periapical del diente 16',
                tooth_number: '16',
                date: '2024-01-15',
                formatted_date: '15/01/2024',
                thumbnail: '/assets/images/thumbnails/periapical_16.jpg',
                url: '/assets/images/full/periapical_16.jpg',
                file_size: '2.4 MB'
            },
            {
                id: 2,
                type: 'bitewing',
                type_name: 'Mordida',
                description: 'Radiografía de mordida bite-wing',
                tooth_number: '3-4',
                date: '2024-01-10',
                formatted_date: '10/01/2024',
                thumbnail: '/assets/images/thumbnails/bite_wing_34.jpg',
                url: '/assets/images/full/bite_wing_34.jpg',
                file_size: '1.8 MB'
            }
        ],
        uploadProgress: {
            show: false,
            percent: 0,
            file: ''
        },
        zoomLevel: 1,
        rotation: 0,

        init() {
            this.loadPatientImages();
        },

        async loadPatientImages() {
            try {
                // In real implementation, this would fetch from API
                // const response = await fetch(`/api/clinical/patients/${this.$root.patientId}/images`);
                // const data = await response.json();
                // if (data.success) {
                //     this.images = data.data;
                // }
            } catch (error) {
                console.error('Error loading images:', error);
            }
        },

        async handleFileUpload(event) {
            const files = event.target.files;
            if (files.length === 0) return;

            this.uploadProgress.show = true;
            this.uploadProgress.file = files[0].name;

            // Simulate upload progress
            for (let i = 0; i <= 100; i++) {
                this.uploadProgress.percent = i;
                await new Promise(resolve => setTimeout(resolve, 20));
            }

            // Process uploaded files
            for (const file of files) {
                await this.processUploadedImage(file);
            }

            this.uploadProgress.show = false;
            this.uploadProgress.percent = 0;
            this.$root.showToast('Imágenes subidas exitosamente', 'success');
        },

        async processUploadedImage(file) {
            // Process image (resize, compress, store)
            // This would integrate with your image processing service
            return new Promise(resolve => setTimeout(resolve, 1000));
        },

        captureImage() {
            // Integrate with camera/device capture API
            this.$root.showToast('Capturando imagen...', 'info');
            setTimeout(() => {
                this.$root.showToast('Imagen capturada exitosamente', 'success');
                // Add to images array
            }, 2000);
        },

        selectCamera() {
            // List available cameras/devices
            this.$root.showToast('Seleccionando cámara...', 'info');
        },

        filterByType(type) {
            this.selectedType = type;
        },

        filteredImages() {
            let filtered = this.images;
            
            if (this.selectedType !== 'all') {
                filtered = filtered.filter(img => img.type === this.selectedType);
            }

            // Apply sorting
            if (this.sortOrder === 'date') {
                filtered.sort((a, b) => new Date(b.date) - new Date(a.date));
            } else if (this.sortOrder === 'type') {
                filtered.sort((a, b) => a.type.localeCompare(b.type));
            }

            return filtered;
        },

        getImageTypeClass(type) {
            const classes = {
                'periapical': 'bg-blue-100 text-blue-700',
                'bitewing': 'bg-green-100 text-green-700',
                'panoramic': 'bg-purple-100 text-purple-700',
                'cephalometric': 'bg-orange-100 text-orange-700'
            };
            return classes[type] || 'bg-gray-100 text-gray-700';
        },

        viewImage(image) {
            this.selectedImage = image;
            this.zoomLevel = 1;
            this.rotation = 0;
        },

        rotateImage(degrees) {
            this.rotation += degrees;
        },

        zoomIn() {
            this.zoomLevel = Math.min(this.zoomLevel * 1.2, 3);
        },

        zoomOut() {
            this.zoomLevel = Math.max(this.zoomLevel / 1.2, 0.5);
        },

        async downloadImage() {
            if (!this.selectedImage) return;
            
            try {
                const link = document.createElement('a');
                link.href = this.selectedImage.url;
                link.download = `dental_${this.selectedImage.type}_${this.selectedImage.tooth_number}_${this.selectedImage.date}.jpg`;
                link.click();
                
                this.$root.showToast('Imagen descargada', 'success');
            } catch (error) {
                this.$root.showToast('Error al descargar imagen', 'error');
            }
        },

        addToPatientRecord() {
            if (!this.selectedImage) return;
            
            // Add image to patient clinical record
            this.$root.showToast('Imagen agregada al expediente del paciente', 'success');
        }
    };
}
</script>