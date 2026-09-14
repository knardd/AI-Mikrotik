import sys
import json
import argparse
import routeros_api

def apply_changes(host, user, password, port, commands_json):
    try:
        commands = json.loads(commands_json)
        connection = routeros_api.RouterOsApiPool(
            host, username=user, password=password, port=int(port), plaintext_login=True
        )
        api = connection.get_api()
        
        executed_results = []
        for cmd in commands:
            # Contoh struktur cmd: {"path": "/ip/firewall/nat", "action": "set", "id": "*1", "params": {"disabled": "no"}}
            path = cmd.get('path')
            action = cmd.get('action', 'add') # add, set, remove, enable, disable
            params = cmd.get('params', {})
            item_id = cmd.get('id')

            resource = api.get_resource(path)
            
            if action == 'add':
                resource.add(**params)
            elif action == 'set' and item_id:
                resource.set(id=item_id, **params)
            elif action == 'enable' and item_id:
                resource.enable(id=item_id)
            elif action == 'disable' and item_id:
                resource.disable(id=item_id)
            elif action == 'remove' and item_id:
                resource.remove(id=item_id)
                
            executed_results.append(f"Sukses eksekusi {action} pada {path}")

        connection.disconnect()
        return {"status": "success", "results": executed_results}
    except Exception as e:
        return {"status": "error", "message": str(e)}

if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument('--host', required=True)
    parser.add_argument('--user', required=True)
    parser.add_argument('--password', required=True)
    parser.add_argument('--port', default=8728)
    parser.add_argument('--commands', required=True)
    args = parser.parse_args()

    result = apply_changes(args.host, args.user, args.password, args.port, args.commands)
    print(json.dumps(result))