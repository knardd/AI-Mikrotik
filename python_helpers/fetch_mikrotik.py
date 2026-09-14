import sys
import json
import argparse
import routeros_api

def fetch_data(host, user, password, port=8728):
    try:
        connection = routeros_api.RouterOsApiPool(
            host, 
            username=user, 
            password=password, 
            port=int(port),
            plaintext_login=True
        )
        api = connection.get_api()
        
        # Ambil data dari modul-modul penting
        data = {
            "ip_address": api.get_resource('/ip/address').get(),
            "ip_route": api.get_resource('/ip/route').get(),
            "firewall_nat": api.get_resource('/ip/firewall/nat').get(),
            "firewall_filter": api.get_resource('/ip/firewall/filter').get(),
            "dhcp_server": api.get_resource('/ip/dhcp-server').get(),
            "dns": api.get_resource('/ip/dns').get(),
        }
        
        connection.disconnect()
        return {"status": "success", "data": data}
    except Exception as e:
        return {"status": "error", "message": str(e)}

if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument('--host', required=True)
    parser.add_argument('--user', required=True)
    parser.add_argument('--password', required=True)  # Diubah dari --pass ke --password
    parser.add_argument('--port', default=8728)
    args = parser.parse_args()

    # Menggunakan args.password (bukan args.pass)
    result = fetch_data(args.host, args.user, args.password, args.port)
    print(json.dumps(result))