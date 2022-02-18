
    <div id="taa-modal-new-password" class="taa-bg-modal taa-d-none">
        <div class="taa-bg-modal-child">
            <div class="taa-modal-header">
                <h2>Crie uma senha</h2>
                <span id="taa-close-new-password" aria-hidden="true" onclick="taaCloseModal(this)" >&times;</span>
            </div>
            <form id="taa-form-new-password" method="post" >
                <p class="taa-text-left taa-mb-15" >Informe uma senha e a confirmação de senha, para concluir a criação da conta:</p>

                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide taa-mb-0">
                    <!-- <input type="hidden" name="form-new-password"> -->
                    <input id="taa-new-pass1" type="password" name="taa-pass" class="woocommerce-Input woocommerce-Input--text input-text taa-mb-15"
                           placeholder="Senha" pattern=".{8,}" title="A senha deve ter no mínimo 8 dígitos" required />
                    <input id="taa-new-pass2" type="password" name="taa-pass-c" class="woocommerce-Input woocommerce-Input--text input-text taa-mb-0"
                           placeholder="Confirmação de senha" pattern=".{8,}" title="A senha deve ter no mínimo 8 dígitos" required />

                    <!-- Error message -->
                    <p id="taa-error-new-pass" class="taa-bg-transp taa-text-center taa-text-danger taa-pt-8 taa-mb-0 taa-d-none" ></p>
                </p>
                <button id="taa-btn-form-new-password" type="submit" class="woocommerce-Button button taa-set-spin" >Criar <div class="taa-d-none taa-spin-load"></div></button>
            </form>
        </div>
    </div>
