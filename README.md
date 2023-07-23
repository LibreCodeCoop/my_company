[![Start contributing](https://img.shields.io/github/issues/LibreCodeCoop/my_company/good%20first%20issue?color=7057ff&label=Contribute)](https://github.com/LibreCodeCoop/my_company/issues?q=is%3Aissue+is%3Aopen+sort%3Aupdated-desc+label%3A%22good+first+issue%22)

# My Company

Get access to important information about your company

## Setup

* Install and configure the registration app
  ```bash
  # registration
  occ app:enable --force registration
  occ config:app:set registration username_policy_regex --value "/^\d{11}$/"
  occ config:app:set registration show_fullname --value yes
  occ config:app:set registration enforce_fullname --value yes
  occ config:app:set registration additional_hint --value "Informe o seu CPF como nome de usuário utilizando apenas números"
  occ config:app:set core shareapi_allow_links_exclude_groups --value "[\"waiting-approval\"]"
  occ config:app:set core shareapi_only_share_with_group_members --value no

  # System settings
  # Disable "Log in with a device" at login screen
  occ config:system:set auth.webauthn.enabled --value false --type boolean

  # Theme
  occ config:app:set theming name --value "LibreCode"
  occ config:app:set theming slogan --value "Feito com ❤️"
  occ config:app:set theming url --value "https://librecode.coop"

  # Group folders
  occ app:enable --force groupfolders
  occ group:add mycompany --display-name="My Company"
  occ groupfolders:create mycompany
  occ groupfolders:group `occ groupfolders:list --output=json|jq '.[]|select(.mount_point=="mycompany")|.id'` mycompany
  ```

## Contributing

[here](.github/CONTRIBUTING.md)
