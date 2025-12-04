// Sistema de Gestión de Leads
class LeadsSystem {
    constructor() {
        this.modal = null;
        this.currentPropertyId = null;
        this.isSubmitting = false;
        this.init();
    }

    init() {
        this.createModal();
        this.createFloatingCTA();
        this.bindEvents();
        this.showFloatingCTAAfterScroll();
    }

    createModal() {
        this.modal = document.createElement('div');
        this.modal.className = 'contact-modal';
        this.modal.innerHTML = `
            <div class="contact-modal-content">
                <div class="contact-modal-header">
                    <h3><i class="fas fa-handshake"></i> ¡Contáctanos!</h3>
                    <p>Estamos aquí para ayudarte a encontrar tu propiedad ideal</p>
                    <button class="close-modal">&times;</button>
                </div>
                
                <div class="contact-form-container">
                    <form class="lead-form" id="leadForm">
                        <div class="form-group">
                            <label for="leadNombre" class="required">Nombre completo</label>
                            <input type="text" id="leadNombre" name="nombre" required 
                                   placeholder="Ej: María García">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="leadEmail" class="required">Email</label>
                                <input type="email" id="leadEmail" name="email" required 
                                       placeholder="maria@email.com">
                            </div>
                            <div class="form-group">
                                <label for="leadTelefono" class="required">Teléfono</label>
                                <input type="tel" id="leadTelefono" name="telefono" required 
                                       placeholder="+57 300 123 4567">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="leadTipoInteres">Tipo de interés</label>
                                <select id="leadTipoInteres" name="tipo_interes">
                                    <option value="alquilar">Quiero alquilar</option>
                                    <option value="comprar">Quiero comprar</option>
                                    <option value="vender">Quiero vender</option>
                                    <option value="consulta">Consulta general</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="leadZonaInteres">Zona de interés</label>
                                <input type="text" id="leadZonaInteres" name="zona_interes" 
                                       placeholder="Ej: Chapinero, Zona Rosa">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="leadPresupuestoMin">Presupuesto mínimo</label>
                                <input type="number" id="leadPresupuestoMin" name="presupuesto_min" 
                                       placeholder="500000" min="0">
                            </div>
                            <div class="form-group">
                                <label for="leadPresupuestoMax">Presupuesto máximo</label>
                                <input type="number" id="leadPresupuestoMax" name="presupuesto_max" 
                                       placeholder="2000000" min="0">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="leadMensaje">Mensaje adicional</label>
                            <textarea id="leadMensaje" name="mensaje" 
                                      placeholder="Cuéntanos qué tipo de propiedad buscas, características específicas, fechas de interés, etc."></textarea>
                        </div>

                        <div class="form-group">
                            <div class="form-group-inline">
                                <input type="checkbox" id="leadAceptaContacto" name="acepta_contacto" checked>
                                <label for="leadAceptaContacto">Acepto ser contactado por WhatsApp o teléfono</label>
                            </div>
                            <div class="form-group-inline">
                                <input type="checkbox" id="leadAceptaMarketing" name="acepta_marketing">
                                <label for="leadAceptaMarketing">Deseo recibir ofertas especiales y noticias</label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-cancel" onclick="leadsSystem.closeModal()">
                                Cancelar
                            </button>
                            <button type="submit" class="btn-submit-lead">
                                <i class="fas fa-paper-plane"></i>
                                Enviar consulta
                            </button>
                        </div>

                        <input type="hidden" name="inmueble_id" id="leadInmuebleId">
                        <input type="hidden" name="fuente" value="web">
                    </form>
                </div>
            </div>
        `;

        document.body.appendChild(this.modal);
    }

    createFloatingCTA() {
        const floatingCTA = document.createElement('button');
        floatingCTA.className = 'cta-floating';
        floatingCTA.id = 'floatingCTA';
        floatingCTA.innerHTML = `
            <i class="fas fa-comments"></i>
            <span>¿Necesitas ayuda?</span>
        `;
        floatingCTA.style.display = 'none';
        floatingCTA.onclick = () => this.openModal();

        document.body.appendChild(floatingCTA);
    }

    bindEvents() {
        // Cerrar modal
        this.modal.querySelector('.close-modal').onclick = () => this.closeModal();
        this.modal.onclick = (e) => {
            if (e.target === this.modal) this.closeModal();
        };

        // Enviar formulario
        document.getElementById('leadForm').onsubmit = (e) => this.submitLead(e);

        // Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.classList.contains('active')) {
                this.closeModal();
            }
        });
    }

    showFloatingCTAAfterScroll() {
        let hasShown = false;
        window.addEventListener('scroll', () => {
            const scrollPercent = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
            
            if (scrollPercent > 25 && !hasShown) {
                const floatingCTA = document.getElementById('floatingCTA');
                if (floatingCTA) {
                    floatingCTA.style.display = 'flex';
                    hasShown = true;
                }
            }
        });
    }

    openModal(propertyId = null, propertyInfo = null) {
        this.currentPropertyId = propertyId;
        
        // Pre-llenar información si viene de una propiedad específica
        if (propertyId) {
            document.getElementById('leadInmuebleId').value = propertyId;
            
            if (propertyInfo) {
                const mensaje = `Estoy interesado en la propiedad: ${propertyInfo.direccion} - ${propertyInfo.tipo} en ${propertyInfo.ciudad}. Precio: $${propertyInfo.precio}`;
                document.getElementById('leadMensaje').value = mensaje;
            }
        }

        this.modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Focus en primer campo
        setTimeout(() => {
            document.getElementById('leadNombre').focus();
        }, 300);
    }

    closeModal() {
        this.modal.classList.remove('active');
        document.body.style.overflow = '';
        this.resetForm();
    }

    resetForm() {
        document.getElementById('leadForm').reset();
        document.getElementById('leadInmuebleId').value = '';
        this.currentPropertyId = null;
    }

    async submitLead(e) {
        e.preventDefault();

        if (this.isSubmitting) return;

        const submitBtn = this.modal.querySelector('.btn-submit-lead');
        const originalText = submitBtn.innerHTML;

        this.isSubmitting = true;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<div class="loading-spinner"></div> Enviando...';

        try {
            const formData = new FormData(e.target);
            
            const response = await fetch('procesar_lead.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('¡Consulta enviada exitosamente!', 'success');
                this.closeModal();

                // Mostrar opción de WhatsApp si está disponible
                if (result.whatsapp_url) {
                    setTimeout(() => {
                        this.showWhatsAppOption(result.whatsapp_url, result.whatsapp_message);
                    }, 1000);
                }

                // Tracking opcional (Google Analytics, Facebook Pixel, etc.)
                this.trackLeadSubmission(result.lead_id);

            } else {
                throw new Error(result.error || 'Error desconocido');
            }

        } catch (error) {
            console.error('Error al enviar lead:', error);
            this.showNotification('Error al enviar la consulta. Inténtalo nuevamente.', 'error');
        } finally {
            this.isSubmitting = false;
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }

    showWhatsAppOption(whatsappUrl, message) {
        const notification = document.createElement('div');
        notification.className = 'notification info';
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                <i class="fab fa-whatsapp" style="font-size: 20px;"></i>
                <strong>¿Quieres una respuesta más rápida?</strong>
            </div>
            <p style="margin: 0 0 15px 0; font-size: 14px; opacity: 0.9;">
                Continúa la conversación por WhatsApp
            </p>
            <div style="display: flex; gap: 10px;">
                <button onclick="window.open('${whatsappUrl}', '_blank')" 
                        style="background: #25d366; border: none; color: white; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-size: 12px;">
                    <i class="fab fa-whatsapp"></i> Abrir WhatsApp
                </button>
                <button onclick="this.parentElement.parentElement.remove()" 
                        style="background: transparent; border: 1px solid white; color: white; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-size: 12px;">
                    Cerrar
                </button>
            </div>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 10000);
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" 
                        style="background: none; border: none; color: white; font-size: 18px; cursor: pointer; padding: 0 5px;">
                    &times;
                </button>
            </div>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.animation = 'fadeOut 0.3s ease forwards';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }

    trackLeadSubmission(leadId) {
        // Google Analytics 4
        if (typeof gtag !== 'undefined') {
            gtag('event', 'lead_submission', {
                lead_id: leadId,
                method: 'contact_form'
            });
        }

        // Facebook Pixel
        if (typeof fbq !== 'undefined') {
            fbq('track', 'Lead', {
                lead_id: leadId
            });
        }

        console.log('Lead tracked:', leadId);
    }

    // Métodos públicos para integración
    openForProperty(propertyId, propertyInfo) {
        this.openModal(propertyId, propertyInfo);
    }

    openGeneral() {
        this.openModal();
    }
}

// Inicializar sistema cuando el DOM esté listo
let leadsSystem;

document.addEventListener('DOMContentLoaded', function() {
    leadsSystem = new LeadsSystem();
});

// Funciones globales para compatibilidad
function openContactModal(propertyId = null, propertyInfo = null) {
    if (leadsSystem) {
        leadsSystem.openModal(propertyId, propertyInfo);
    }
}

function openGeneralContact() {
    if (leadsSystem) {
        leadsSystem.openGeneral();
    }
}

// Función para agregar CTAs a propiedades existentes
function addLeadCTAToProperty(propertyElement, propertyId, propertyInfo) {
    const existingCTA = propertyElement.querySelector('.btn-cta-card');
    if (existingCTA) return; // Ya existe

    const cta = document.createElement('button');
    cta.className = 'btn-cta-card';
    cta.innerHTML = '<i class="fas fa-envelope"></i> Consultar';
    cta.onclick = () => openContactModal(propertyId, propertyInfo);

    const actionsContainer = propertyElement.querySelector('.property-actions');
    if (actionsContainer) {
        actionsContainer.appendChild(cta);
    }
}

// Auto-agregar CTAs a propiedades al cargar
document.addEventListener('DOMContentLoaded', function() {
    // Buscar todas las tarjetas de propiedades y agregar CTAs
    document.querySelectorAll('.property-card').forEach(card => {
        const propertyId = card.dataset.id;
        if (propertyId) {
            const propertyInfo = extractPropertyInfoFromCard(card);
            addLeadCTAToProperty(card, propertyId, propertyInfo);
        }
    });
});

function extractPropertyInfoFromCard(card) {
    const title = card.querySelector('.property-title')?.textContent || '';
    const location = card.querySelector('.property-location')?.textContent || '';
    const price = card.querySelector('.property-price')?.textContent || '';
    const type = card.querySelector('.feature span')?.textContent || '';

    return {
        direccion: title.trim(),
        ciudad: location.replace(/.*,\s*/, '').trim(),
        precio: price.replace(/[^\d]/g, ''),
        tipo: type
    };
}