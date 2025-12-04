// Sistema de Comparación Universal
class CompareSystem {
    constructor() {
        this.compareItems = JSON.parse(localStorage.getItem('compareProperties') || '[]');
        this.maxItems = 3;
        this.widget = null;
        this.preview = null;
        this.init();
    }

    init() {
        this.createWidget();
        this.updateWidget();
        this.bindEvents();
    }

    createWidget() {
        // Crear widget flotante
        this.widget = document.createElement('div');
        this.widget.className = 'compare-floating-widget';
        this.widget.innerHTML = `
            <div class="compare-mini-preview">
                <div class="compare-mini-header">
                    <h4><i class="fas fa-balance-scale"></i> Comparar</h4>
                    <button class="btn-close-preview" onclick="compareSystem.togglePreview()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="compare-mini-list"></div>
                <div class="compare-actions">
                    <button class="btn-compare-now" onclick="compareSystem.goToComparison()" disabled>
                        <i class="fas fa-eye"></i> Comparar Ahora
                    </button>
                    <button class="btn-clear-compare" onclick="compareSystem.clearAll()">
                        <i class="fas fa-trash"></i> Limpiar Todo
                    </button>
                </div>
            </div>
            
            <button class="compare-fab" onclick="compareSystem.togglePreview()">
                <i class="fas fa-balance-scale"></i>
                <span>Comparar</span>
                <span class="compare-badge">0</span>
            </button>
        `;

        document.body.appendChild(this.widget);
        this.preview = this.widget.querySelector('.compare-mini-preview');
    }

    updateWidget() {
        const badge = this.widget.querySelector('.compare-badge');
        const compareBtn = this.widget.querySelector('.btn-compare-now');
        const list = this.widget.querySelector('.compare-mini-list');

        // Actualizar contador
        badge.textContent = this.compareItems.length;

        // Mostrar/ocultar widget
        if (this.compareItems.length > 0) {
            this.widget.classList.add('active');
        } else {
            this.widget.classList.remove('active');
            this.preview.classList.remove('active');
        }

        // Habilitar/deshabilitar botón
        compareBtn.disabled = this.compareItems.length < 2;

        // Actualizar lista
        this.updatePreviewList();

        // Actualizar checkboxes en la página
        this.updateCheckboxes();
    }

    async updatePreviewList() {
        const list = this.widget.querySelector('.compare-mini-list');
        
        if (this.compareItems.length === 0) {
            list.innerHTML = '<p style="color: #7f8c8d; text-align: center; margin: 20px 0;">No hay propiedades seleccionadas</p>';
            return;
        }

        // Obtener datos de las propiedades
        try {
            const response = await fetch('get_favorites.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ids: this.compareItems })
            });

            if (response.ok) {
                const properties = await response.json();
                this.renderPreviewList(properties);
            }
        } catch (error) {
            console.error('Error al cargar propiedades:', error);
        }
    }

    renderPreviewList(properties) {
        const list = this.widget.querySelector('.compare-mini-list');
        list.innerHTML = '';

        properties.forEach(property => {
            const imageUrl = property.foto ? 
                `../${property.foto}` : 
                'https://via.placeholder.com/60x45/e0e0e0/666666?text=Sin+Imagen';

            const item = document.createElement('div');
            item.className = 'compare-mini-item';
            item.innerHTML = `
                <img src="${imageUrl}" alt="${property.dir_inm}" class="compare-mini-image" 
                     onerror="this.src='https://via.placeholder.com/60x45/e0e0e0/666666?text=Sin+Imagen'">
                <div class="compare-mini-info">
                    <div class="compare-mini-title">${this.truncateText(property.dir_inm, 25)}</div>
                    <div class="compare-mini-price">$${new Intl.NumberFormat('es-CO').format(property.precio_alq)}</div>
                </div>
                <button class="btn-remove-compare" onclick="compareSystem.removeItem(${property.cod_inm})">
                    <i class="fas fa-times"></i>
                </button>
            `;
            list.appendChild(item);
        });
    }

    toggleItem(propertyId) {
        const index = this.compareItems.indexOf(propertyId);

        if (index === -1) {
            // Agregar
            if (this.compareItems.length >= this.maxItems) {
                this.showNotification(`Solo puedes comparar hasta ${this.maxItems} propiedades`, 'warning');
                return false;
            }
            this.compareItems.push(propertyId);
            this.showNotification('Agregado para comparar', 'success');
        } else {
            // Remover
            this.compareItems.splice(index, 1);
            this.showNotification('Removido de comparación', 'info');
        }

        this.saveToStorage();
        this.updateWidget();
        return true;
    }

    removeItem(propertyId) {
        const index = this.compareItems.indexOf(propertyId);
        if (index !== -1) {
            this.compareItems.splice(index, 1);
            this.saveToStorage();
            this.updateWidget();
            this.showNotification('Removido de comparación', 'info');
        }
    }

    clearAll() {
        if (this.compareItems.length === 0) return;
        
        if (confirm('¿Seguro que deseas limpiar todas las propiedades seleccionadas para comparar?')) {
            this.compareItems = [];
            this.saveToStorage();
            this.updateWidget();
            this.preview.classList.remove('active');
            this.showNotification('Lista de comparación limpiada', 'info');
        }
    }

    togglePreview() {
        if (this.compareItems.length === 0) {
            this.showNotification('Selecciona propiedades para comparar', 'info');
            return;
        }
        
        this.preview.classList.toggle('active');
    }

    goToComparison() {
        if (this.compareItems.length < 2) {
            this.showNotification('Selecciona al menos 2 propiedades para comparar', 'warning');
            return;
        }

        // Guardar en favoritos para que aparezcan en la página de comparación
        const currentFavorites = JSON.parse(localStorage.getItem('favoriteProperties') || '[]');
        const newFavorites = [...new Set([...currentFavorites, ...this.compareItems])];
        localStorage.setItem('favoriteProperties', JSON.stringify(newFavorites));

        // Ir a favoritos con vista de comparación
        const url = `favoritos.php?compare=${this.compareItems.join(',')}`;
        window.location.href = url;
    }

    updateCheckboxes() {
        // Actualizar todos los checkboxes en la página
        document.querySelectorAll('.compare-checkbox-input').forEach(checkbox => {
            const propertyId = parseInt(checkbox.dataset.propertyId);
            checkbox.checked = this.compareItems.includes(propertyId);
        });
    }

    bindEvents() {
        // Event delegation para checkboxes dinámicos
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('compare-checkbox-input')) {
                const propertyId = parseInt(e.target.dataset.propertyId);
                const success = this.toggleItem(propertyId);
                
                if (!success) {
                    e.target.checked = false;
                }
            }
        });
    }

    saveToStorage() {
        localStorage.setItem('compareProperties', JSON.stringify(this.compareItems));
    }

    showNotification(message, type = 'info') {
        const colors = {
            success: '#27ae60',
            warning: '#f39c12',
            info: '#3498db',
            error: '#e74c3c'
        };

        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: ${colors[type]};
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            z-index: 10001;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            animation: slideInFromTop 0.3s ease;
        `;
        notification.textContent = message;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'fadeOut 0.3s ease forwards';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    truncateText(text, maxLength) {
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }

    // Método para usar desde HTML
    isSelected(propertyId) {
        return this.compareItems.includes(propertyId);
    }
}

// Crear instancia global
let compareSystem;

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    compareSystem = new CompareSystem();
});

// Funciones globales para compatibilidad
function toggleCompareProperty(propertyId) {
    if (compareSystem) {
        return compareSystem.toggleItem(propertyId);
    }
    return false;
}

// Estilos adicionales para animaciones
const additionalStyles = `
    @keyframes slideInFromTop {
        from {
            transform: translateX(-50%) translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
        to {
            opacity: 0;
            transform: translateX(-50%) translateY(-20px);
        }
    }
`;

if (!document.getElementById('compare-system-styles')) {
    const styleSheet = document.createElement('style');
    styleSheet.id = 'compare-system-styles';
    styleSheet.textContent = additionalStyles;
    document.head.appendChild(styleSheet);
}