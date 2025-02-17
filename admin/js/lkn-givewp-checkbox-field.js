const cx = window.classNames
const { __ } = window.wp.i18n
const { registerBlockType, getCategories, setCategories } = window.wp.blocks
const { PanelBody, PanelRow, TextControl, ToggleControl, CheckboxControl, SVG, Path } = window.wp.components
const { useBlockProps, InspectorControls } = window.wp.blockEditor
const { createElement } = window.wp.element

const blockProps = {
  name: 'givewp/lkn-form-checkbox',
  title: __('GiveWP Checkbox', 'antispam-donation-for-givewp'),
  description: __('A simple checkbox block for GiveWP forms.', 'antispam-donation-for-givewp'),
  category: 'custom',
  icon: () => {
    return createElement(
      SVG,
      {
        xmlns: 'http://www.w3.org/2000/svg',
        width: '20',
        height: '20',
        viewBox: '0 0 20 20',
        fill: 'none'
      },
      createElement(
        Path,
        {
          fillRule: 'evenodd',
          clipRule: 'evenodd',
          d: 'M15.8385 8.04847e-07L4.16146 8.04847e-07C3.63433 -1.63023e-05 3.17954 -3.10596e-05 2.80497 0.030572C2.40963 0.062873 2.01641 0.134189 1.63803 0.326983C1.07354 0.614603 0.614603 1.07354 0.326983 1.63803C0.134189 2.01641 0.062873 2.40963 0.030572 2.80497C-3.10596e-05 3.17954 -1.63023e-05 3.63429 8.04846e-07 4.16142L8.04846e-07 15.8385C-1.63023e-05 16.3657 -3.10596e-05 16.8205 0.030572 17.195C0.062873 17.5904 0.134189 17.9836 0.326983 18.362C0.614603 18.9265 1.07354 19.3854 1.63803 19.673C2.01641 19.8658 2.40963 19.9371 2.80497 19.9694C3.17954 20 3.6343 20 4.16144 20H15.8386C16.3657 20 16.8205 20 17.195 19.9694C17.5904 19.9371 17.9836 19.8658 18.362 19.673C18.9265 19.3854 19.3854 18.9265 19.673 18.362C19.8658 17.9836 19.9371 17.5904 19.9694 17.195C20 16.8205 20 16.3657 20 15.8386V4.16144C20 3.6343 20 3.17954 19.9694 2.80497C19.9371 2.40963 19.8658 2.01641 19.673 1.63803C19.3854 1.07354 18.9265 0.614603 18.362 0.326983C17.9836 0.134189 17.5904 0.062873 17.195 0.030572C16.8205 -3.10596e-05 16.3657 -1.63023e-05 15.8385 8.04847e-07ZM15.2071 7.70711C15.5976 7.31658 15.5976 6.68342 15.2071 6.29289C14.8166 5.90237 14.1834 5.90237 13.7929 6.29289L8.5 11.5858L6.20711 9.29289C5.81658 8.90237 5.18342 8.90237 4.79289 9.29289C4.40237 9.68342 4.40237 10.3166 4.79289 10.7071L7.79289 13.7071C8.18342 14.0976 8.81658 14.0976 9.20711 13.7071L15.2071 7.70711Z',
          fill: 'currentColor'
        }
      )
    )
  },
  attributes: {
    label: {
      type: 'string',
      default: 'Custom Checkbox Label'
    },
    isChecked: {
      type: 'boolean',
      default: false
    },
    isRequired: {
      type: 'boolean',
      default: false
    },
    isCheckedByDefault: {
      type: 'boolean',
      default: false
    },
    showInAdmin: {
      type: 'boolean',
      default: true
    },
    showInReceipt: {
      type: 'boolean',
      default: true
    }
  },
  supports: {
    multiple: true
  },
  edit: (props) => {
    const { attributes, setAttributes } = props
    const blockProps = useBlockProps()

    const checkboxClassName = cx({ 'give-is-required': true })

    return createElement(
      'div',
      blockProps,
      createElement('div', { className: 'lkn-form-checkbox-container', style: { display: 'flex', gap: '6px', alignItems: 'center' } },
        createElement(CheckboxControl, {
          label: attributes.label,
          checked: attributes.isCheckedByDefault,
          required: attributes.isRequired,
          onChange: () => null,
          readOnly: true,
          class: checkboxClassName,
          __nextHasNoMarginBottom: true
        })
      ),

      createElement(InspectorControls, {},
        createElement(PanelBody, { title: __('Field Settings', 'antispam-donation-for-givewp'), initialOpen: true },
          createElement(PanelRow, {},
            createElement(TextControl, {
              label: __('Label', 'antispam-donation-for-givewp'),
              value: attributes.label,
              onChange: (value) => setAttributes({ label: value })
            })
          ),
          createElement(PanelRow, {},
            createElement(ToggleControl, {
              label: __('Required', 'antispam-donation-for-givewp'),
              checked: attributes.isRequired,
              onChange: (checked) => setAttributes({ isRequired: checked })
            })
          ),
          createElement(PanelRow, {},
            createElement(ToggleControl, {
              label: __('Checked by Default', 'antispam-donation-for-givewp'),
              checked: attributes.isCheckedByDefault,
              onChange: (checked) => setAttributes({ isCheckedByDefault: checked })
            })
          )
        ),

        createElement(PanelBody, { title: __('Display Settings', 'antispam-donation-for-givewp'), initialOpen: true },
          createElement(PanelRow, {},
            createElement(ToggleControl, {
              label: __('Show in Admin Panel', 'antispam-donation-for-givewp'),
              checked: attributes.showInAdmin,
              onChange: (checked) => setAttributes({ showInAdmin: checked })
            })
          ),
          createElement(PanelRow, {},
            createElement(ToggleControl, {
              label: __('Show in Receipt', 'antispam-donation-for-givewp'),
              checked: attributes.showInReceipt,
              onChange: (checked) => setAttributes({ showInReceipt: checked })
            })
          )
        )
      )
    )
  },
  save: ({ attributes }) => {
    return null
  }
}

if (window.givewp && window.givewp.form && window.givewp.form.blocks) {
  const existingCategories = getCategories()
  const newCategories = [
    { slug: 'input', title: __('Input Fields', 'antispam-donation-for-givewp') },
    { slug: 'custom', title: __('Custom', 'antispam-donation-for-givewp') },
    { slug: 'section', title: __('Layout', 'antispam-donation-for-givewp') }
  ]

  const categoriesToAdd = newCategories.filter(
    ({ slug }) => !existingCategories.some(cat => cat.slug === slug)
  )

  if (categoriesToAdd.length) {
    setCategories([...existingCategories, ...categoriesToAdd])
  }

  window.givewp.form.blocks.register('givewp/lkn-form-checkbox', {
    ...blockProps,
    save: () => null
  })
}

registerBlockType('givewp/lkn-form-checkbox', { ...blockProps })
