import fs from 'node:fs/promises';
import {randomUUID} from 'node:crypto';
import {tmpdir} from 'node:os';
import path from 'node:path';
import {execa} from 'execa';
import jpegtran from 'jpegtran-bin';
import {fileTypeFromBuffer} from 'file-type';
import {imageDimensionsFromData} from 'image-dimensions';
import {assertUint8Array} from 'uint8array-extras';

export default function imageminJpegtran(options = {}) {
	return async function (data) {
		assertUint8Array(data);

		const fileType = await fileTypeFromBuffer(data);
		if (!fileType || fileType.mime !== 'image/jpeg') {
			return data;
		}

		const dimensions = imageDimensionsFromData(data);
		if (dimensions?.width === 0 && dimensions?.height === 0) {
			return data;
		}

		const arguments_ = ['-copy', 'none'];

		if (options.progressive) {
			arguments_.push('-progressive');
		}

		if (options.arithmetic) {
			arguments_.push('-arithmetic');
		} else {
			arguments_.push('-optimize');
		}

		const inputPath = path.join(tmpdir(), `input-${randomUUID()}.jpg`);
		const outputPath = path.join(tmpdir(), `output-${randomUUID()}.jpg`);

		arguments_.push('-outfile', outputPath, inputPath);

		await fs.writeFile(inputPath, data);

		try {
			await execa(jpegtran, arguments_);
			return await fs.readFile(outputPath);
		} finally {
			// Clean up temporary files
			await Promise.all([
				fs.unlink(inputPath).catch(() => {}),
				fs.unlink(outputPath).catch(() => {}),
			]);
		}
	};
}
