<template>
    <nav v-if="isVisible && isMounted" class="overlay">
      <div class="modal">

        <div class="buttons">
            <button :class="mode === 0 ? 'btn1' : 'btn2'" type="button" @click="mode = 0">
              Cloud
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M8.00001 7.00116H16.75C18.8567 7.00116 19.9101 7.00116 20.6667 7.5069C20.9943 7.72584 21.2756 8.00717 21.4944 8.33484C21.9796 9.06117 21.9992 10.0608 22 12.0026V13.0029M12 7.00116L11.3666 5.73392C10.8418 4.68406 10.3622 3.6273 9.19927 3.19106C8.68991 3 8.10803 3 6.94428 3C5.1278 3 4.21957 3 3.53807 3.38043C3.05227 3.65161 2.65142 4.05257 2.38032 4.53851C2 5.22021 2 6.12871 2 7.94571V11.0023C2 15.7177 2 18.0754 3.46447 19.5403C4.70529 20.7815 6.58688 20.9711 10 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M13 18.6667C13 19.9553 14.0074 21 15.25 21H19.975C21.0934 21 22 20.0598 22 18.9C22 17.7402 21.0833 16.8 19.9649 16.8C20.0897 15.3643 18.9799 14 17.5 14C16.2055 14 15.1431 15.0307 15.0342 16.3439C13.8928 16.4566 13 17.4535 13 18.6667Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            </button>
            <button :class="mode === 1 ? 'btn1' : 'btn2'" type="button" @click="mode = 1">
                Upload
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                    <path d="M12 15L12 5M12 15C11.2998 15 9.99153 13.0057 9.5 12.5M12 15C12.7002 15 14.0085 13.0057 14.5 12.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M5 19H19.0001" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>

            <button type="button" @click="this.$emit('url', '');">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" color="#ff5555" fill="none">
                <path d="M18 6L12 12M12 12L6 18M12 12L18 18M12 12L6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>
            
        </div>
        <div class="list2" v-if="mode === 0">
          <div v-for="(image) in images" 
          :key="image"
          >
          <!-- Informations principales du produit -->
           <div class="boxContainer" style="max-width: 150px; min-width: 150px; flex-direction: column; padding: 5px;">
            <button type="button" @click="shareLink(image)"> 
              <img v-if="image.startsWith('https')" :src="image" :alt="'image'" style="min-height : 150px; max-height: 150px; min-width: 150px; max-width: 150px;"/>
              <img v-else :src="'https://management.hoggari.com' + image" :alt="'image'" style="min-height : 150px; max-height: 150px; min-width: 150px; max-width: 150px;"/>
              
            </button>
            <button type="button" class="cancel" @click="deleteImage(image)">
              delete
            </button>
           </div>

        </div>
        </div>
        <div v-else>
          <div :style="{display: 'flex', justifyContent: 'center', alignItems: 'center', flexDirection: 'column', marginBlock: '10px'}" 
          @dragover.prevent 
          @dragenter.prevent 
          @drop="handleCatDrop">
              <h2>Showcase Category image</h2>
              <div>
                <label for="imageUpload2" class="inputImg">
                  <span v-if="!descriptionImage">Place square image here</span>
                  <img 
                    v-else 
                    :src="descriptionImage" 
                    alt="Preview" 
                    style="max-width: 200px; max-height: 200px;" 
                  />
                </label>
                <input
                  ref="fileInput"
                  class="hiddenInput"
                  id="imageUpload2"
                  type="file"
                  accept="image/*"
                  @change="handleCatImageUpload"
                />
              </div>
            </div>
  
            <button v-if="descriptionImage" type="button" class="confirm" @click="saveCat">
              Upload & Place it
            </button>
        </div>


        <!--p>{{ message }}</p>
        <div class="buttons">
          <button class="confirm" @click="this.$emit('confirm')">Yes</button>
          <button class="cancel" @click="this.$emit('cancel')">No</button>
        </div-->
      </div>
    </nav>
</template>
  
  <script>


  export default {
    name: 'Uploader',
    data() {
      return {
        isMounted: false,
        visible: false,
        descriptionImage: null,
        descriptionBlob: null,
        savedImageUrl: '',
        mode: 0,
        images: [],
        fileInput: null,
      }
    },
    props: {
        isVisible: {
            type: Boolean,
            default: false, // Valeur booléenne par défaut
        },
        message: {
            type: String,
            default: "You confirm this action ?",
        },
    },
    mounted() {
      
      this.getDescriptionImages();
      this.visible = this.isVisible;
      this.isMounted = true;
    },
    methods: {
        shareLink(url) {
          this.$emit('url', url);
          this.visible = false;
          
        },

        async deleteImage(url) {
          const response = await fetch('https://management.hoggari.com/backend/api.php?action=deleteImages', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({ url }),
          });
          
          const result = await response.json();
          if(result.success) {
            this.images = [];
            this.getDescriptionImages();
          }
          console.log(result);
        },


        async getDescriptionImages() {
          try {
            const response = await fetch('https://management.hoggari.com/backend/api.php?action=descriptionImages');
            if (!response.ok) throw new Error('Failed to fetch images');

            const data = await response.json();
            if (data.success) {
              this.images = data.data; // Stocker les images dans une propriété de données
            } else {
              console.error(data.message);
            }
          } catch (error) {
            console.error('Error:', error.message);
          }
        },


        handleCatDrop(event) {
            event.preventDefault();
            
            const file = event.dataTransfer.files[0];
            this.handleCatImageUpload({ target: { files: [file] } });
        },

        handleCatImageUpload(event) {
        const file = event.target.files[0];
      
        const allowedTypes = ['image/webp', 'image/png', 'image/jpeg', 'image/jpg'];
          if (!allowedTypes.includes(file.type)) {
            console.error("rejected, Invalid image type. Only WebP, PNG, and JPG are allowed.")
            return;
          }

        if (!file) {
          console.error("No file selected.");
          return;
        }
          if (file instanceof Blob) {
          const reader = new FileReader();
          reader.onload = (e) => {
            this.descriptionImage = e.target.result; // Sauvegarde l'image
            this.fileInput.value = '';
          };
          reader.onerror = (error) => {
            console.error("Error reading file:", error);
          };
          this.descriptionBlob = file;
          reader.readAsDataURL(file); // Lit le fichier en DataURL
        } else {
          console.error("The selected file is not a valid Blob.");
        }

        
      },

      processImage(file) {

      return new Promise((resolve, reject) => {
        const allowedTypes = ['image/webp', 'image/png', 'image/jpeg', 'image/jpg'];
        const MAX_SIZE = 5 * 1024 * 1024; // 5 MB
        console.log("file: ", file);
        if (!file || !allowedTypes.includes(file.type)) {
          return reject(new Error('Invalid image type. Only WebP, PNG, and JPG are allowed.'));
        }

        if (file.size > MAX_SIZE) {
          return reject(new Error('Image too large.'));
        }


        const img = new Image();
        const reader = new FileReader();

        // Gestion du CORS si besoin
        img.crossOrigin = 'anonymous';

        reader.onload = (e) => {
          img.src = e.target.result;

          img.onload = () => {
            const size = Math.min(img.width, img.height);
            const canvas = document.createElement('canvas');
            canvas.width = size;
            canvas.height = size;

            const ctx = canvas.getContext('2d');
            ctx.drawImage(
              img,
              (img.width - size) / 2,
              (img.height - size) / 2,
              size,
              size,
              0,
              0,
              size,
              size
            );

            canvas.toBlob(
              (blob) => {
                if (blob) {
                  const formData = new FormData();
                  formData.append('image', blob, 'category.webp');
                  resolve(formData);
                } else {
                  reject(new Error('Blob creation failed.'));
                }
              },
              'image/webp',
              0.8
            );
          };

          img.onerror = () => reject(new Error('Image load failed: ', file.type));
        };

        reader.onerror = () => reject(new Error('File read failed.'));
        reader.readAsDataURL(file);
      });
      },

      async saveCat() {
        if (!this.descriptionBlob) {
          console.log('Please upload an image.');
          return;
        }

        if (!(this.descriptionBlob instanceof Blob)) {
          console.log('The uploaded file is not a valid image.');
          return;
        }

        try {
          console.log('Processing image, please wait...');
          const formData = await this.processImage(this.descriptionBlob);
          const fileName = this.descriptionBlob.name.replace(/\.[^/.]+$/, '');
          console.log('Nom du fichier sans extension :', fileName);
          formData.append('image', this.descriptionBlob); // 'image' est le nom du champ, 'categoryBlob' est la valeur
          formData.append('imageName', fileName);
          formData.append('table', 'description_catalogue');

          // Envoi au serveur
          console.log('Uploading image, please wait...');
          const response = await fetch('https://management.hoggari.com/backend/api.php?action=uploadImages', {
            method: 'POST',
            body: formData,
          });

          if (!response.ok) {
            console.error('Error uploading image.');
            return;
          }
          console.log('Response received, waiting for result...');
          const result = await response.json();
          this.savedImageUrl = result.data;
          this.getDescriptionImages();
          this.shareLink(this.savedImageUrl);

        } catch (error) {
          console.error(`Error: ${error}`);
        }
      }
    },
  };
  </script>
  
  <style>
  .overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
  }
  
  .modal {
    width: 90%;
    height: 80%;
    background: var(--color-whitly);
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
  }
  .dark .modal {
    background: var(--color-darkly);
  }
  
  .buttons {
    margin-top: 15px;
    display: flex;
    justify-content: center;
    gap: 10px;
  }
  
  .buttons button {
    padding: 10px 15px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
  }
  
  .confirm {
    background-color: #4caf50;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    color: white;
  }
  
  .cancel {
    background-color: #f44336;
    color: white;
  }
  </style>
  