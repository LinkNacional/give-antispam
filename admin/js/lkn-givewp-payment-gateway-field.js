const { addFilter } = window.wp.hooks
const { Fragment, useState, useEffect } = window.wp.element
let formId = 0
const apiClicked = false

/**
 * Modifica o bloco de Gateway de Pagamento do GiveWP para adicionar novos atributos.
 */
function lknModifyGiveWPGatewayBlock(settings, name) {
  if (name !== 'givewp/form-payment-gateway') {
    return settings
  }

  settings.attributes.lknDisabledGateways = {
    type: 'object',
    default: {}
  }

  return settings
}
addFilter(
  'blocks.registerBlockType',
  'lkn-customize-givewp-gateway',
  lknModifyGiveWPGatewayBlock
)

function lknCustomizeGatewayEdit(BlockEdit) {
  return (props) => {
    if (props.name !== 'givewp/payment-gateways') {
      return createElement(BlockEdit, props)
    }

    const { attributes, setAttributes } = props
    const [paymentGateways, setPaymentGateways] = useState([])

    // Busca a lista de métodos de pagamento do GiveWP
    useEffect(() => {
      // Obtém o parâmetro donationFormID da URL
      const urlParams = new URLSearchParams(window.location.search)
      formId = urlParams.get('donationFormID')

      if (formId) {
        // Passando o formId como parâmetro para a rota da API
        window.wp.apiFetch({ path: '/lkn-antispam/v1/payment-gateways' })
          .then((response) => {
            if (response && Array.isArray(response)) {
              setPaymentGateways(response)

              if (!attributes.lknDisabledGateways) {
                const gatewayList = response.reduce((acc, gateway) => {
                  acc[gateway.id] = true
                  return acc
                }, {})

                setAttributes({
                  lknDisabledGateways: gatewayList
                })
              }
            }
          })
          .catch((error) => console.error('Erro ao buscar os métodos de pagamento:', error))
      } else {
        console.error('Form ID não encontrado na URL.')
      }
    }, [])

    useEffect(() => {
      // Função do evento de clique
      function handleButtonClick(event) {
        event.preventDefault()

        // Verifique se o botão está desabilitado
        if (button.hasAttribute('disabled') || button.getAttribute('aria-disabled') === 'true') {
          return
        }

        if (!apiClicked) {
          window.wp.apiFetch({
            path: '/lkn-antispam/v1/update-gateways',
            method: 'POST',
            data: {
              formId,
              gatewaysList: attributes.lknDisabledGateways
            }
          })
            .then(() => {
              button.click()
            })
            .catch((error) => {
              console.error('Erro ao buscar os gateways desativados:', error)

              // Em caso de erro, reabilite o botão também
              button.removeAttribute('aria-disabled')
              button.removeAttribute('disabled')
            })
        }
      }

      const button = document.querySelector('.components-button.is-primary[aria-disabled]')

      if (!button) {
        return
      }

      button.removeEventListener('click', handleButtonClick)

      // Adiciona o novo evento de clique
      button.addEventListener('click', handleButtonClick)

      return () => {
        button.removeEventListener('click', handleButtonClick)
      }
    }, [attributes])

    // Atualiza o estado dos switches
    function toggleGateway(gatewayId) {
      setAttributes({
        lknDisabledGateways: {
          ...attributes.lknDisabledGateways,
          [gatewayId]: !(
            attributes.lknDisabledGateways &&
            attributes.lknDisabledGateways[gatewayId]
          )
        }
      })
    }

    return createElement(
      Fragment,
      {},
      createElement(BlockEdit, props),
      createElement(
        InspectorControls,
        {},
        paymentGateways.map((gateway) =>
          createElement(
            PanelBody,
            { title: gateway.label, initialOpen: true },
            createElement(
              PanelRow,
              { key: gateway.id },
              createElement(ToggleControl, {
                label: __('Enable this payment method', 'antispam-donation-for-givewp'),
                checked: !!(attributes.lknDisabledGateways && attributes.lknDisabledGateways[gateway.id]),
                onChange: () => toggleGateway(gateway.id)
              })
            )
          )
        )
      )
    )
  }
}
addFilter(
  'editor.BlockEdit',
  'lkn-customize-givewp-gateway-edit',
  lknCustomizeGatewayEdit
)
