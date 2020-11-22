import subprocess
import time
import shutil
import zipfile
import os
import json
import hashlib
from datetime import datetime
from pprint import pprint

def main():
    def sha1(str_text):
        encoded = str_text.encode('utf-8')
        myhash = hashlib.sha1(encoded)
        return myhash.hexdigest()
    def isOfStatic(path):
        static_files = [
            "content/static/bundles",
            "content/static/images",
            "content/static/css/fonts/icons.woff"
        ]
        for f in static_files:
            if path.startswith(f): return True
        return False
    def isOfDev(path):
        dev_files = [
            "content/static/beta",
            "content/static/css",
            "content/static/external",
            "content/static/js",
            "content/root",
            "content/static/service.js",
        ]
        for f in dev_files:
            if path.startswith(f): return True
        return False


    # Paths
    current_path = "C:/wamp64/www/ffonline/"
    onedrive_path = "C:/Users/obaid/OneDrive/FFONLINE_backups/"
    github_path = "C:/Users/obaid/OneDrive/Documents/GitHub/wp_ffonline/"

    # Bundle Write
    startTime = time.time()
    # Raw Urls
    static_path = current_path + "content/static/"

    upload = input("Upload to SSH?").lower() == "y"
    unzip = False
    if upload:
        unzip = input("Replace Content?").lower() == "y"


    with open(static_path + "mix.json", "r",encoding="utf8") as dump:
        mix_ref = json.loads(dump.read())
    with open(static_path + "index.json", "r",encoding="utf8") as dump:
        index = json.loads(dump.read())

    def raw_urls(bundle_name):
        bundle = index[bundle_name]
        urls = {"css":[],"js":[]}
        filetypes = urls.keys()

        for mix in bundle["mix"]:
            for filetype in filetypes:
                urls[filetype] = urls[filetype] + mix_ref[mix][filetype]
        shouldEdit = False
        for filetype in filetypes:
            lastEdited = 0
            urls[filetype] = urls[filetype] + bundle[filetype]
            urls[filetype] = [static_path + url + "." + filetype for url in urls[filetype]]
            for fullname in urls[filetype]:
                try:
                    lastEdited = max(lastEdited,os.path.getmtime(fullname))
                except:
                    pass
            bundle_file = static_path + "bundles/" + bundle_name + "-" + bundle.get(filetype + "_hash","") + "." + filetype
            try:
                if os.path.getmtime(bundle_file) <= lastEdited:
                    shouldEdit = True
            except:
                shouldEdit = True
        if shouldEdit:
            return urls
        return False

    for bundle_name,bundle in index.items():
        urls = raw_urls(bundle_name)
        if urls == False:
            continue
        # Minify JS
        cmd = "terser " + " ".join(urls["js"]) + " --compress --mangle --output " + current_path + "temp.js"
        subprocess.call(cmd,shell=True)
        # Minify CSS
        css_input = ""
        for filename in urls["css"]:
            with open(filename, "r",encoding="utf8") as dump:
                css_input += dump.read()
        with open(current_path + "temp_input.css","w+",encoding="utf8") as dump:
            dump.write(css_input)
        cmd = "cssnano < " + current_path + "temp_input.css" + " > " + current_path + "temp.css"
        subprocess.call(cmd,shell=True)

        # Put Minified In
        for filetype in ['css','js']:
            with open(current_path + "temp." + filetype, "r",encoding="utf8") as dump:
                minified = dump.read()
            hashed = sha1(minified)
            index[bundle_name][filetype + "_hash"] = hashed
            try:
                os.remove(static_path + "bundles/" + bundle_name + "-" + bundle.get(filetype + "_hash","") + "." + filetype)
            except:
                pass
            with open(static_path + "bundles/" + bundle_name + "-" + hashed + "." + filetype, "w+",encoding="utf8") as dump:
                dump.write(minified)
        print("----------------------" + bundle_name + "------------------------")
    # Put New Index In
    with open(static_path + "index.json","w+",encoding="utf8") as dump:
        dump.write(json.dumps(index,indent=4))
    # Clean Up Temp File
    for clean_file in ["temp.css","temp_input.css","temp.js"]:
        try:
            os.remove(current_path + clean_file)
        except:
            pass
    

    print("Bundles Compiled")
    print("Time took: " + str(time.time() - startTime))
    
    # Delete from github folder
    github_files = os.listdir(github_path)
    github_files.remove('.git')
    github_files.remove('.gitattributes')
    github_files.remove('.gitignore')
    github_files.remove('README.md')

    for f in github_files:
        path = github_path.rstrip('/') + "/" + f
        if os.path.isfile(path):
            os.remove(path)
        else:
            shutil.rmtree(path)
    print("Old files deleted from GitHub")

    # Copy to GitHub
    files = os.listdir(current_path + "content/")
    for f in files:
        path = current_path + "content/" + f
        print(path)
        if os.path.isdir(path):
            shutil.copytree(path,github_path + f)
        else:
            shutil.copy(path,github_path)

    print("Copied to Github folder")

    # Archive For Production
    static_zip = zipfile.ZipFile(current_path + 'static.zip', 'w', zipfile.ZIP_DEFLATED)
    main_zip = zipfile.ZipFile(current_path + 'content.zip', 'w', zipfile.ZIP_DEFLATED)
    for root,dirs,files in os.walk(current_path + "content"):
        for f in files:
            ZipPath = os.path.join(root, f)
            relativePath = ZipPath.replace(current_path,"").replace("\\",'/')
            if isOfStatic(relativePath):
                static_zip.write(ZipPath,arcname=relativePath.replace("content/static/",""))
            elif isOfDev(relativePath):
                pass
            else:
                main_zip.write(ZipPath,arcname=relativePath)
    main_zip.close()
    static_zip.close()
    print("Produced")

    # Copy to Onedrive
    current_time = str(int(time.time()))
    shutil.copy(current_path + "content.zip",onedrive_path + "content-" + current_time + ".zip")
    shutil.copy(current_path + "static.zip",onedrive_path + "static-" + current_time + ".zip")
    print("Zipped to OneDrive")
    
    # Upload
    if upload:
        host = "root@167.99.11.178"
        sep = ":"
        upload_to = "/home/runcloud/webapps/"
        cmd = "scp " + current_path + "content.zip "  + host + sep  + upload_to
        subprocess.call(cmd,shell=True)
        cmd = "scp " + current_path + "static.zip "  + host + sep  + upload_to
        subprocess.call(cmd,shell=True)
        print("Uploaded")
        unzip = False
        if unzip:
            ssh_connection = "ssh " + host + " "
            # Content
            cmd = ssh_connection + " rm -r " + upload_to + "fanfiction_online/content" 
            subprocess.call(cmd,shell=True)
            cmd = ssh_connection + " unzip " + upload_to + "content.zip" + " -d " + upload_to + "fanfiction_online/" 
            subprocess.call(cmd,shell=True)
            cmd = ssh_connection + " rm " + upload_to + "content.zip" 
            subprocess.call(cmd,shell=True)

            # Static
            cmd = ssh_connection + " rm -r " + upload_to + "static/*"
            subprocess.call(cmd,shell=True)
            cmd = ssh_connection + " unzip " + upload_to + "static.zip" + " -d " + upload_to + "static/" 
            subprocess.call(cmd,shell=True)
            cmd = ssh_connection + " rm " + upload_to + "static.zip" 
            subprocess.call(cmd,shell=True)
            print("Replaced")
    
    input("Completed")

try:
    main()
except Exception as e:
    print(e)
    input("Exit?")
