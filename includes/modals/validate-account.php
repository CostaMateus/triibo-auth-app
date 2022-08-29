
    <div id="taa-modal-validate-account" class="taa-bg-modal taa-d-none">
        <div class="taa-bg-modal-child">
            <div class="taa-modal-header">
                <h2>Entre com sua conta</h2>
                <span id="taa-close-validate-account" aria-hidden="true" onclick="taaCloseModal(this)" >&times;</span>
            </div>
            <form id="taa-form-validate-account" method="post" >
                <p class="taa-text-left taa-mb-15" >Informe o e-mail e a senha da sua conta:</p>

                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide taa-mb-0">
                    <input id="taa-validate-acc-email" type="email" name="taa-email" class="woocommerce-Input woocommerce-Input--text input-text taa-mb-15"
                           placeholder="E-mail" required />
                    <input id="taa-validate-acc-pass" type="password" name="taa-pass" class="woocommerce-Input woocommerce-Input--text input-text taa-mb-0"
                           placeholder="Senha" pattern=".{8,}" title="A senha deve ter no mínimo 8 dígitos" required />

                    <!-- Error message -->
                    <p id="taa-error-validate-acc" class="taa-bg-transp taa-text-center taa-text-danger taa-pt-8 taa-mb-0 taa-d-none" ></p>
                </p>
                <button id="taa-btn-form-validate-account" type="submit" class="woocommerce-Button button taa-set-spin" >Entrar <div class="taa-d-none taa-spin-load"></div></button>
            </form>
        </div>
    </div>
