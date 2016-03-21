Feature: Generate certificates

  Scenario: generate a simple certificate
    Given the following acme_php configuration:
    """
    domains:
        acmephp.com: ~
    """
    When I run the command "acmephp:generate"
    Then "1" certificate should be generated
    And the certificate for the domain "acmephp.com" should contains:
    """
    subject: acmephp.com
    selfSigned: false
    sANs: [acmephp.com]
    """

  Scenario: generate a certificate with
    Given the following acme_php configuration:
    """
    domains:
        acmephp.sc:
          country: SC
          state: Seychelles
          locality: La Digue
          organization_name: Vacation
          organization_unit_name: Lazy

    """
    When I run the command "acmephp:generate"
    Then "1" certificate should be generated
    And the certificate for the domain "acmephp.sc" should contains:
    """
    subject: acmephp.sc
    """

  Scenario: generate multiple certificates
    Given the following acme_php configuration:
    """
    domains:
        acmephp.fr: ~
        acmephp.es: ~

    """
    When I run the command "acmephp:generate"
    Then "2" certificate should be generated

  Scenario: generate multiple certificates with failures
    Given the following acme_php configuration:
    """
    domains:
        "*.acmephp.fr": ~
        acmephp.es: ~

    """
    When I run the command "acmephp:generate"
    Then the command should be exit with code "1"
     And "1" certificate should be generated
     And a certificate exists for the domain "acmephp.es"
