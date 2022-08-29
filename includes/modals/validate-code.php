
    <div id="taa-modal-validate-code" class="taa-bg-modal taa-d-none taa-animated">
        <div class="taa-bg-modal-child">
            <div class="taa-modal-header">
                <h2>Entre com o código</h2>
                <span id="taa-close-validate-code" aria-hidden="true" onclick="taaCloseModal(this)" >&times;</span>
            </div>
            <form id="taa-form-validate-code" method="post" >
                <p class="taa-text-left taa-mb-15" >Enviamos um e-mail com seu código de confirmação, informe-o abaixo.</p>

                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide mb-0">
                    <input id="taa-email" type="hidden" name="email" />
                    <input id="taa-code" type="text" class="woocommerce-Input woocommerce-Input--text input-text taa-mb-0" maxlength="6"
                           name="code" placeholder="Código" pattern="[0-9]{6}" title="O código tem seis (6) números" required />

                    <!-- Error message -->
                    <p id="taa-error-validate-code" class="taa-bg-transp taa-text-center taa-text-danger taa-pt-8            taa-mb-0 taa-d-none" ></p>
                    <p id="taa-resent-code"         class="taa-bg-transp taa-float-left  taa-text-dark   taa-pb-20 taa-mt-20 taa-mb-0 taa-d-none" >Código reenviado</p>
                    <a id="taa-resend-code"         class="taa-bg-transp taa-float-right taa-pointer     taa-pb-20 taa-mt-20 taa-mb-0 taa-d-none" onclick="taaResendCodeEmail()" >Reenviar código</a>
                </p>
                <button id="taa-btn-form-validate-code" type="submit" class="woocommerce-Button button taa-set-spin" >Confirmar <div class="taa-d-none taa-spin-load"></div></button>
            </form>
        </div>
    </div>
