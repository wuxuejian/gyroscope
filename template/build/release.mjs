import path from 'path'
import fsExtra from 'fs-extra'
const { existsSync, remove, copy } = fsExtra
const cwd = process.cwd()
//打包发布路径，谨慎改动
const releaseRelativePath = '/Users/lunatic/Tuoluojiang/CRMEB-OA/oa-own/public/admin'
const distPath = path.resolve(cwd, 'dist')
const releasePath = path.resolve(cwd, releaseRelativePath)
async function build() {
    if (existsSync(releasePath)) {
        await remove(releasePath)
    }
    console.log(`文件正在复制 ==> ${releasePath}`)
    try {
        await copyFile(distPath, releasePath)
    } catch (error) {
        console.log(`\n ${error}`)
    }
    console.log(`文件已复制 ==> ${releasePath}`)
}

function copyFile(sourceDir, targetDir) {
    return new Promise((resolve, reject) => {
        copy(sourceDir, targetDir, (err) => {
            if (err) {
                reject(err)
            } else {
                resolve()
            }
        })
    })
}

build()
