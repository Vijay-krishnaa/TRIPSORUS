// Shared JavaScript functions
document.addEventListener('DOMContentLoaded', function() {
    // Image Preview for Upload
    const imageInputs = document.querySelectorAll('.image-upload');
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const previewId = this.getAttribute('data-preview');
            const preview = document.getElementById(previewId);
            preview.innerHTML = '';
            
            if (this.files && this.files.length > 0) {
                const maxFiles = parseInt(this.getAttribute('data-max-files') || 10;
                
                if (this.files.length > maxFiles) {
                    alert(`Maximum ${maxFiles} images allowed!`);
                    this.value = '';
                    return;
                }
                
                Array.from(this.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imgContainer = document.createElement('div');
                        imgContainer.style.position = 'relative';
                        imgContainer.style.display = 'inline-block';
                        imgContainer.style.margin = '5px';
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.classList.add('img-preview');
                        
                        const removeBtn = document.createElement('button');
                        removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                        removeBtn.classList.add('btn', 'btn-danger', 'btn-sm');
                        removeBtn.style.position = 'absolute';
                        removeBtn.style.top = '5px';
                        removeBtn.style.right = '5px';
                        removeBtn.onclick = function() {
                            imgContainer.remove();
                        };
                        
                        imgContainer.appendChild(img);
                        imgContainer.appendChild(removeBtn);
                        preview.appendChild(imgContainer);
                    };
                    reader.readAsDataURL(file);
                });
            }
        });
    });
    
    // Activate current page in sidebar
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    const navLinks = document.querySelectorAll('.sidebar .nav-link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage || (currentPage === 'index.php' && href === '#')) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
    
    // Form submission handlers
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formName = this.getAttribute('id') || 'form';
            alert(`${formName.replace(/([A-Z])/g, ' $1')} submitted successfully!`);
            this.reset();
            const previews = this.querySelectorAll('.image-preview-container');
            previews.forEach(preview => preview.innerHTML = '');
        });
    });
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});