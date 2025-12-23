/**
 * Sistema de Gestión de Leads - Optimizado para Cloudinary
 */
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
                    <button class="close-modal" aria-label="Cerrar">&times;</button>
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
                                      placeholder="Cuéntanos qué tipo de propiedad buscas..."></textarea>
                        </div>

                        <div class="form-group">
                            <div class="form-group-inline">
                                <input type="checkbox" id="leadAceptaContacto" name="acepta_contacto" checked>
                                <label for="leadAceptaContacto">Acepto ser contactado por WhatsApp o teléfono</label>
                            </div>
                            <div class="form-group-inline">
                                <input type="checkbox" id="leadAceptaMarketing" name="acepta_marketing">
                                <label for="leadAceptaMarketing">Deseo recibir ofertas y noticias</label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-cancel" id="btnCancelModal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn-submit-lead">
                                <i class="fas fa-paper-plane"></i> Enviar consulta
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
        if (document.getElementById('floatingCTA')) return;

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
        this.modal.querySelector('#btnCancelModal').onclick = () => this.closeModal();

        this.modal.onclick = (e) => {
            if (e.target === this.modal) this.closeModal();
        };

        // Enviar formulario
        const form = document.getElementById('leadForm');
        if (form) {
            form.onsubmit = (e) => this.submitLead(e);
        }

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
        const mensajeInput = document.getElementById('leadMensaje');
        const idInput = document.getElementById('leadInmuebleId');

        if (propertyId && idInput) {
            idInput.value = propertyId;
            if (propertyInfo && mensajeInput) {
                mensajeInput.value = `Interés en: ${propertyInfo.direccion} (${propertyInfo.tipo}) en ${propertyInfo.ciudad}. Precio ref: ${propertyInfo.precio}`;
            }
        }

        this.modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        setTimeout(() => {
            const nombreInput = document.getElementById('leadNombre');
            if (nombreInput) nombreInput.focus();
        }, 300);
    }

    closeModal() {
        this.modal.classList.remove('active');
        document.body.style.overflow = '';
        this.resetForm();
    }

    resetForm() {
        const form = document.getElementById('leadForm');
        if (form) form.reset();
        const idInput = document.getElementById('leadInmuebleId');
        if (idInput) idInput.value = '';
        this.currentPropertyId = null;
    }

    async submitLead(e) {
        e.preventDefault();
        if (this.isSubmitting) return;

        const submitBtn = this.modal.querySelector('.btn-submit-lead');
        const originalContent = submitBtn.innerHTML;

        this.isSubmitting = true;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

        try {
            const formData = new FormData(e.target);
            const response = await fetch('procesar_lead.php', {
                method: 'POST',
                body: formData
            });

            // Verificamos si la respuesta es OK antes de parsear JSON
            if (!response.ok) throw new Error('Error en el servidor');

            const result = await response.json();

            if (result.success) {
                this.showNotification('¡Consulta enviada exitosamente!', 'success');
                this.closeModal();

                if (result.whatsapp_url) {
                    setTimeout(() => {
                        this.showWhatsAppOption(result.whatsapp_url);
                    }, 800);
                }
                this.trackLeadSubmission(result.lead_id);
            } else {
                throw new Error(result.error || 'Error al procesar');
            }
        } catch (error) {
            console.error('Error Lead System:', error);
            this.showNotification('No pudimos enviar tu mensaje. Revisa tu conexión.', 'error');
        } finally {
            this.isSubmitting = false;
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalContent;
        }
    }

    showWhatsAppOption(whatsappUrl) {
        const notify = document.createElement('div');
        notify.className = 'notification info wa-notice';
        notify.innerHTML = `
            <div style="margin-bottom: 8px;"><strong>¿Prefieres WhatsApp?</strong></div>
            <button onclick="window.open('${whatsappUrl}', '_blank')" class="btn-wa-direct">
                <i class="fab fa-whatsapp"></i> Hablar ahora
            </button>
        `;
        document.body.appendChild(notify);
        setTimeout(() => notify.remove(), 8000);
    }

    showNotification(message, type = 'info') {
        const note = document.createElement('div');
        note.className = `notification ${type}`;
        note.innerHTML = `<span>${message}</span>`;
        document.body.appendChild(note);
        setTimeout(() => {
            note.style.opacity = '0';
            setTimeout(() => note.remove(), 500);
        }, 4000);
    }

    trackLeadSubmission(leadId) {
        if (typeof gtag !== 'undefined') {
            gtag('event', 'generate_lead', { 'lead_id': leadId });
        }
    }
}

// Inicialización Global
let leadsSystem;
document.addEventListener('DOMContentLoaded', () => {
    leadsSystem = new LeadsSystem();

    // Auto-detectar tarjetas de propiedades
    document.querySelectorAll('.property-card').forEach(card => {
        const pid = card.dataset.id;
        if (pid) {
            const info = extractPropertyInfoFromCard(card);
            const btnContainer = card.querySelector('.property-actions');
            if (btnContainer && !card.querySelector('.btn-cta-card')) {
                const btn = document.createElement('button');
                btn.className = 'btn-cta-card';
                btn.innerHTML = '<i class="fas fa-envelope"></i>';
                btn.onclick = (e) => {
                    e.preventDefault();
                    leadsSystem.openModal(pid, info);
                };
                btnContainer.appendChild(btn);
            }
        }
    });
});

function extractPropertyInfoFromCard(card) {
    return {
        direccion: card.querySelector('.property-title')?.textContent.trim() || 'Propiedad sin título',
        ciudad: card.querySelector('.property-location')?.textContent.trim() || '',
        precio: card.querySelector('.property-price')?.textContent.trim() || 'Consultar',
        tipo: card.querySelector('.feature span')?.textContent.trim() || 'Inmueble'
    };
}