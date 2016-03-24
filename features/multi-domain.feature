Feature: Generate certificates

  Scenario: generate a certificate with alternative names
    Given the following acme_php configuration:
    """
    domains:
        acmephp.com:
            subject_alternative_names:
                - www.acmephp.com
                - sales.acmephp.com
    """
    When I run the command "acmephp:generate"
    Then "1" certificate should be generated
    And the certificate for the domain "acmephp.com" should contains:
    """
    subject: acmephp.com
    subjectAlternativeNames: [acmephp.com, www.acmephp.com, sales.acmephp.com]
    """

  Scenario: add an alternative name to an existing certificate
    Given a "acmephp.com" certificate
    And the following acme_php configuration:
    """
    domains:
        acmephp.com:
            subject_alternative_names:
                - www.acmephp.com
    """
    When I run the command "acmephp:generate"
    Then the certificate for the domain "acmephp.com" should contains:
    """
    subject: acmephp.com
    subjectAlternativeNames: [acmephp.com, www.acmephp.com]
    """

  Scenario: add an alternative name to an existing certificate
    Given a "acmephp.com" certificate which contains:
    """
    subjectAlternativeNames: [acmephp.com, www.acmephp.com]
    """
    And the following acme_php configuration:
    """
    domains:
        acmephp.com:
            subject_alternative_names:
                - www.acmephp.com
    """
    When I run the command "acmephp:generate"
    Then the certificate for the domain "acmephp.com" should contains:
    """
    subject: acmephp.com
    subjectAlternativeNames: [acmephp.com, www.acmephp.com]
    """

  Scenario: remove an alternative name to an existing certificate
    Given a "acmephp.com" certificate which contains:
    """
    subjectAlternativeNames: [www.acmephp.com]
    """
    And the following acme_php configuration:
    """
    domains:
        acmephp.com: ~
    """
    When I run the command "acmephp:generate"
    Then the certificate for the domain "acmephp.com" should contains:
    """
    subject: acmephp.com
    subjectAlternativeNames: [acmephp.com]
    """