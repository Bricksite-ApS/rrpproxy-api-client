# CentralNic(previously named Rrpproxy) http api client 
Minimal api client for CentralNic http api

[CentralNic documentation](https://kb.centralnicreseller.com/api/api-commands/api-command-reference)

## Install
`composer require bricksitedevelopment/rrpproxy-api-client`

## Example usage
### Check availability of a single domain: [CheckDomain](https://kb.centralnicreseller.com/api/api-command/CheckDomain)

    use Bricksite\CentralNic\CentralNicClient;

    $rrp = new CentralNicClient('USERNAME', 'PASSWORD', false);

    $result = $rrp->request('CheckDomain', ['domain' => 'domain.com']);

### Get status information for a single domain: [StatusDomain](https://kb.centralnicreseller.com/api/api-command/StatusDomain)

    use Bricksite\CentralNic\CentralNicClient;

    $rrp = new CentralNicClient('USERNAME', 'PASSWORD', false);

    $result = $rrp->request('StatusDomain', ['domain' => 'domain.com']);

## License
MIT
